<?php namespace Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\Metadata\PropertyMetadata;

interface TypeToSchemaHandlerInterface
{
    /**
     * @param ExtractionContextInterface $extractionContext
     * @param PropertyMetadata $propertyMetadata
     * @return Schema|null
     */
    public function extractSchemaFromType(
        PropertyMetadata $propertyMetadata,
        ExtractionContextInterface $extractionContext
    );
}