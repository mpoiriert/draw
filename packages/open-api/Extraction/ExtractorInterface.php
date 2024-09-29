<?php

namespace Draw\Component\OpenApi\Extraction;

interface ExtractorInterface
{
    /**
     * Return if the extractor can extract the requested data or not.
     */
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool;

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void;
}
