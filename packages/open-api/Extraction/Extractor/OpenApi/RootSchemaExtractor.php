<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Root;
use JMS\Serializer\SerializerInterface;

class RootSchemaExtractor implements ExtractorInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!is_string($source)) {
            return false;
        }

        if (!is_object($target)) {
            return false;
        }

        if (!$target instanceof Root) {
            return false;
        }

        $schema = json_decode($source, true);
        if (JSON_ERROR_NONE != json_last_error()) {
            return false;
        }

        if (!array_key_exists('swagger', $schema)) {
            return false;
        }

        if ('2.0' != $schema['swagger']) {
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
     * @param string $source
     * @param Root   $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $result = $this->serializer->deserialize($source, get_class($target), 'json');

        foreach ($result as $key => $value) {
            $target->{$key} = $value;
        }
    }
}
