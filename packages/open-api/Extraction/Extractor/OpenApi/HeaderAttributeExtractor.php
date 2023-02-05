<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Header;
use Draw\Component\OpenApi\Schema\Response;
use Draw\Component\OpenApi\Schema\Schema;

class HeaderAttributeExtractor implements ExtractorInterface
{
    public static function getDefaultPriority(): int
    {
        return 128;
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$target instanceof Schema) {
            return false;
        }

        if (!$extractionContext->hasParameter('controller-reflection-method')) {
            return false;
        }

        if (!$this->getHeaders($extractionContext->getParameter('controller-reflection-method'))) {
            return false;
        }

        if (!$extractionContext->hasParameter('response')) {
            return false;
        }

        if (!$extractionContext->getParameter('response') instanceof Response) {
            return false;
        }

        return true;
    }

    /**
     * @param \ReflectionMethod $source
     * @param Schema            $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $response = $extractionContext->getParameter('response');

        \assert($response instanceof Response);

        foreach ($this->getHeaders($extractionContext->getParameter('controller-reflection-method')) as $header) {
            $response->headers[$header->name] = $header;
        }
    }

    /**
     * @return Header[]
     */
    private function getHeaders(\ReflectionMethod $reflectionMethod): array
    {
        $attributes = $reflectionMethod->getAttributes(Header::class, \ReflectionAttribute::IS_INSTANCEOF);

        $result = [];
        foreach ($attributes as $attribute) {
            $result[] = $attribute->newInstance();
        }

        return $result;
    }
}
