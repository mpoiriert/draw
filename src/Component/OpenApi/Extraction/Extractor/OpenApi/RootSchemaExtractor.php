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

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param $type
     * @param ExtractionContextInterface $extractionContext
     * @return boolean
     */
    public function canExtract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if (!is_string($source)) {
            return false;
        }

        if (!is_object($type)) {
            return false;
        }

        if (!$type instanceof Root) {
            return false;
        }

        $schema = json_decode($source, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            return false;
        }

        if (!array_key_exists('swagger', $schema)) {
            return false;
        }

        if ($schema['swagger'] != '2.0') {
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
     * @param Root $rootSchema
     * @param ExtractionContextInterface $extractionContext
     */
    public function extract($source, $rootSchema, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $rootSchema, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $result = $this->serializer->deserialize($source, get_class($rootSchema), 'json');

        foreach ($result as $key => $value) {
            $rootSchema->{$key} = $value;
        }
    }
}