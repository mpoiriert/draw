<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Doctrine\Common\Annotations\Reader;
use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\BaseParameter;
use Draw\Component\OpenApi\Schema\Operation;

class ParameterExtractor implements ExtractorInterface
{
    private Reader $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
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
     *
     * @throws ExtractionImpossibleException
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        foreach ($this->reader->getMethodAnnotations($source) as $annotation) {
            if ($annotation instanceof BaseParameter) {
                $target->parameters[] = $annotation;
                $extractionContext->getOpenApi()->extract(
                    $annotation,
                    $annotation,
                    $extractionContext
                );
            }
        }
    }
}
