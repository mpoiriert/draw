<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Doctrine\Common\Annotations\Reader;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Operation as SupportedTarget;
use Draw\Component\OpenApi\Schema\Tag;
use ReflectionMethod as SupportedSource;

class TagExtractor implements ExtractorInterface
{
    /**
     * @var Reader
     */
    private $annotationReader;

    public function __construct(Reader $reader)
    {
        $this->annotationReader = $reader;
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param SupportedSource $source
     * @param SupportedTarget $target
     *
     * @return bool
     */
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$source instanceof SupportedSource) {
            return false;
        }

        if (!$target instanceof SupportedTarget) {
            return false;
        }

        return true;
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param SupportedSource $source
     * @param SupportedTarget $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext)
    {
        foreach ($this->annotationReader->getMethodAnnotations($source) as $annotation) {
            if ($annotation instanceof Tag) {
                $target->tags[] = $annotation->name;
            }
        }
    }
}
