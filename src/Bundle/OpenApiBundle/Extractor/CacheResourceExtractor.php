<?php

namespace Draw\Bundle\OpenApiBundle\Extractor;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Resource\ResourceInterface;

class CacheResourceExtractor implements ExtractorInterface
{
    private $resources = [];

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext)
    {
        return $source instanceof \Reflector;
    }

    /**
     * @param \Reflector $source
     * @param mixed      $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        switch (true) {
            case $source instanceof \ReflectionMethod:
            case $source instanceof \ReflectionClass:
                $this->addCacheResource(new FileResource($source->getFileName()));
                break;
        }
    }

    public function addCacheResource(ResourceInterface $resource)
    {
        $this->resources[] = $resource;
    }

    /**
     * @return array|ResourceInterface[]
     */
    public function getResources(): array
    {
        return $this->resources;
    }
}
