<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Doctrine\Common\Annotations\Reader;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\BaseParameter;
use Draw\Component\OpenApi\Schema\Operation;
use ReflectionMethod;

class ParameterExtractor implements ExtractorInterface
{
    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if(!$source instanceof ReflectionMethod) {
            return false;
        }

        if(!$target instanceof Operation) {
            return false;
        }

        return true;
    }

    /**
     * @param ReflectionMethod $source
     * @param Operation $target
     * @param ExtractionContextInterface $extractionContext
     * @throws ExtractionImpossibleException
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        foreach($this->reader->getMethodAnnotations($source) as $annotation) {
            if($annotation instanceof BaseParameter) {
                $target->parameters[] = $annotation;
            }
        }
    }
}