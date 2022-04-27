<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\PropertiesExtractor;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Root;

class VersioningRootSchemaExtractor implements ExtractorInterface
{
    public static function getDefaultPriority(): int
    {
        return 1000;
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$target instanceof Root) {
            return false;
        }

        if ($target->info->version) {
            return false;
        }

        if (!$extractionContext->getParameter('api.version')) {
            return false;
        }

        return true;
    }

    /**
     * @param string $source
     * @param Root   $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $target->info->version = $extractionContext->getParameter('api.version');

        $extractionContext->setParameter(
            PropertiesExtractor::CONTEXT_PARAMETER_ENABLE_VERSION_EXCLUSION_STRATEGY,
            true
        );
    }
}
