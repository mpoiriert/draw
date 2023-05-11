<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\PhpReflection;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\Response;

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

        if (!\in_array($returnType->getName(), ['void', 'null'])) {
            return;
        }

        if (\array_key_exists(204, $target->responses)) {
            return;
        }

        $response = new Response();
        $response->description = 'When the operation succeed, not content is returned.';
        $target->responses[204] = $response;
    }
}
