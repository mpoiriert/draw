<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Caching;

use Draw\Component\OpenApi\Exception\ExtractionCompletedException;
use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Root;
use Symfony\Component\Config\ConfigCache;

class LoadFromCacheExtractor implements ExtractorInterface
{
    private bool $debug;

    private string $cacheDirectory;

    public static function getDefaultPriority(): int
    {
        return 9999;
    }

    public function __construct(bool $debug, string $cacheDirectory)
    {
        $this->debug = $debug;
        $this->cacheDirectory = $cacheDirectory;
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$target instanceof Root) {
            return false;
        }

        return true;
    }

    /**
     * @param Root $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $version = $extractionContext->getParameter('api.version');

        $path = $this->cacheDirectory.'/openApi-'.$version.'.php';

        $cache = new ConfigCache($path, $this->debug);

        if (!$cache->isFresh()) {
            return;
        }

        $result = require $path;

        foreach ($result as $key => $value) {
            $target->{$key} = $value;
        }

        $target->vendor['X-DrawOpenApi-FromCache'] = true;

        throw new ExtractionCompletedException();
    }
}
