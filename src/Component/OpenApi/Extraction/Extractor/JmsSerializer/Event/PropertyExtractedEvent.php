<?php namespace Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\Event;

use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\Metadata\PropertyMetadata;
use Symfony\Contracts\EventDispatcher\Event;

class PropertyExtractedEvent extends Event
{
    private $propertyMetadata;
    private $schema;

    public function __construct(PropertyMetadata $propertyMetadata, Schema $schema)
    {
        $this->propertyMetadata = $propertyMetadata;
        $this->schema = $schema;
    }

    public function getPropertyMetadata(): PropertyMetadata
    {
        return $this->propertyMetadata;
    }

    public function getSchema(): Schema
    {
        return $this->schema;
    }
}