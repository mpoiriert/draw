<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\Metadata\PropertyMetadata;

class DynamicObjectHandler implements TypeToSchemaHandlerInterface
{
    public function extractSchemaFromType(
        PropertyMetadata $propertyMetadata,
        ExtractionContextInterface $extractionContext
    ): ?Schema {
        if (!($type = $this->getDynamicObjectType($propertyMetadata))) {
            return null;
        }

        $propertySchema = new Schema();
        $propertySchema->type = 'object';
        $propertySchema->additionalProperties = new Schema();
        $propertySchema->additionalProperties->type = $type;

        return $propertySchema;
    }

    private function getDynamicObjectType(PropertyMetadata $item): ?string
    {
        return match (true) {
            !isset($item->type['name']), !\in_array($item->type['name'], ['array', 'ArrayCollection']), !isset($item->type['params'][0]['name']), 'string' != $item->type['params'][0]['name'], !isset($item->type['params'][1]['name']) => null,
            default => $item->type['params'][1]['name'],
        };
    }
}
