<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\BaseParameter;
use Draw\Component\OpenApi\Schema\Operation;

class ParameterExtractor implements ExtractorInterface
{
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
     *
     * @throws ExtractionImpossibleException
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        foreach ($source->getAttributes(BaseParameter::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $attribute = $attribute->newInstance();

            $exists = false;
            foreach ($target->parameters as $index => $parameter) {
                if ($parameter->name === $attribute->name) {
                    $exists = true;
                    $target->parameters[$index] = $attribute;

                    break;
                }
            }

            if (!$exists) {
                $target->parameters[] = $attribute;
            }

            $extractionContext->getOpenApi()->extract(
                $attribute,
                $attribute,
                $extractionContext
            );
        }
    }
}
