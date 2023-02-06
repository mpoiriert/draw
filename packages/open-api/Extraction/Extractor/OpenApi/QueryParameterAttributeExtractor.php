<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\QueryParameter;

class QueryParameterAttributeExtractor implements ExtractorInterface
{
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$source instanceof \ReflectionMethod) {
            return false;
        }

        if (!$target instanceof Operation) {
            return false;
        }

        if (!$this->getQueryParametersAttributes($source)) {
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

        foreach ($this->getQueryParametersAttributes($source) as $queryParameter) {
            $target->parameters[] = $queryParameter;
            $extractionContext->getOpenApi()->extract(
                $queryParameter,
                $queryParameter,
                $extractionContext
            );
        }
    }

    /**
     * @return QueryParameter[]
     */
    private function getQueryParametersAttributes(\ReflectionMethod $reflectionMethod): array
    {
        $result = [];
        foreach ($reflectionMethod->getParameters() as $parameter) {
            $attribute = $parameter->getAttributes(QueryParameter::class, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

            if (!$attribute) {
                continue;
            }

            $result[] = $attribute = $attribute->newInstance();

            \assert($attribute instanceof QueryParameter);

            $attribute->name ??= $parameter->getName();
        }

        return $result;
    }
}
