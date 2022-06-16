<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Caching;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;

class FileTrackingExtractor implements ExtractorInterface
{
    /**
     * @var string[]
     */
    private array $files = [];

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        return ($source instanceof \ReflectionMethod || $source instanceof \ReflectionClass) && $source->getFileName();
    }

    /**
     * @param \ReflectionMethod|\ReflectionClass $source
     * @param mixed                              $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $this->files[$source->getFileName()] = true;
    }

    /**
     * @return array|string[]
     */
    public function clearFiles(): array
    {
        $files = array_keys($this->files);
        $this->files = [];

        return $files;
    }
}
