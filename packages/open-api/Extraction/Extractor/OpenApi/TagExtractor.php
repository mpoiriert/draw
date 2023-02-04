<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Doctrine\Common\Annotations\Reader;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\Tag;

class TagExtractor implements ExtractorInterface
{
    public function __construct(private Reader $annotationReader)
    {
    }

    public static function getDefaultPriority(): int
    {
        return 128;
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
        foreach ($this->annotationReader->getMethodAnnotations($source) as $annotation) {
            if ($annotation instanceof Tag) {
                $target->tags[] = $annotation->name;
            }
        }
    }
}
