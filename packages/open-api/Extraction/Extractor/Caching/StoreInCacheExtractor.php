<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Caching;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Root;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;

class StoreInCacheExtractor implements ExtractorInterface
{
    public static function getDefaultPriority(): int
    {
        return -9999;
    }

    public function __construct(
        private FileTrackingExtractor $fileTrackingExtractor,
        private bool $debug,
        private string $cacheDirectory
    ) {
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$target instanceof Root) {
            return false;
        }

        return true;
    }

    /**
     * @param Root  $target
     * @param mixed $source
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $files = $this->fileTrackingExtractor->clearFiles();

        if (!$extractionContext->getParameter('api.cacheable')) {
            return;
        }

        $cacheKey = $extractionContext->getCacheKey();

        $path = $this->cacheDirectory.'/openApi-'.$cacheKey.'.php';

        $configCache = new ConfigCache($path, $this->debug);

        $metadata = [];
        foreach ($files as $file) {
            $metadata[] = new FileResource($file);
        }

        $target->setVendorDataKey('X-DrawOpenApi-CachedAt', gmdate('Y-m-d H:i:s'));

        $configCache->write(
            '<?php return unserialize('.var_export(serialize($target), true).');',
            $metadata
        );
    }
}
