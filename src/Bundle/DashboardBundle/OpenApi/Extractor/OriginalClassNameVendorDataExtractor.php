<?php

namespace Draw\Bundle\DashboardBundle\OpenApi\Extractor;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Schema;
use ReflectionClass;

class OriginalClassNameVendorDataExtractor implements ExtractorInterface
{
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$source instanceof ReflectionClass) {
            return false;
        }

        if (!$target instanceof Schema) {
            return false;
        }

        return true;
    }

    /**
     * @param ReflectionClass $source
     * @param Schema          $target
     *
     * @throws ExtractionImpossibleException
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $target->setVendorDataKey('x-draw-dashboard-class-name', $source->name);
    }
}
