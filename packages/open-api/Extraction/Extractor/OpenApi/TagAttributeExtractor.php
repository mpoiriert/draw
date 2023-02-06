<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\Tag;

class TagAttributeExtractor implements ExtractorInterface
{
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
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        foreach ($source->getAttributes(Tag::class, \ReflectionAttribute::IS_INSTANCEOF) as $tag) {
            $target->tags[] = $tag->newInstance()->name;
        }
    }
}
