<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\PhpReflection;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\Response;
use Draw\Component\OpenApi\Schema\Schema;

class OperationResponseExtractor implements ExtractorInterface
{
    public static function getDefaultPriority(): int
    {
        return -256;
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$source instanceof \ReflectionMethod) {
            return false;
        }

        if (!$target instanceof Operation) {
            return false;
        }

        return true;
    }

    /**
     * @param \ReflectionMethod $source
     * @param Operation         $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $returnType = $source->getReturnType();

        if (!$returnType instanceof \ReflectionNamedType) {
            return;
        }

        if (\in_array($returnType->getName(), ['void', 'null'])) {
            if (\array_key_exists(204, $target->responses)) {
                return;
            }

            $response = new Response();
            $response->description = 'When the operation succeed, no content is returned.';
            $target->responses[204] = $response;

            return;
        }

        $response = new Response();
        $statusCode = $this->extractStatusCode($returnType->getName(), $response, $extractionContext, $source);

        if (!\array_key_exists($statusCode, $target->responses)) {
            $response->description = 'Operation is successful.';
            $target->responses[$statusCode] = $response;
        }
    }

    private function extractStatusCode(
        string $type,
        Response $response,
        ExtractionContextInterface $extractionContext,
        \ReflectionMethod $source,
    ): int {
        $response->schema = $responseSchema = new Schema();
        $subContext = $extractionContext->createSubContext();
        $subContext->setParameter('controller-reflection-method', $source);
        $subContext->setParameter('response', $response);
        $extractionContext->getOpenApi()->extract($type, $responseSchema, $subContext);

        return $subContext->getParameter('response-status-code', 200);
    }
}
