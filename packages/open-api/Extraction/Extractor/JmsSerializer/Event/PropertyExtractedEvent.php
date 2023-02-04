<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\Event;

use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\Metadata\PropertyMetadata;
use Symfony\Contracts\EventDispatcher\Event;

class PropertyExtractedEvent extends Event
{
    public function __construct(private PropertyMetadata $propertyMetadata, private Schema $schema)
    {
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
