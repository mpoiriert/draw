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
        switch (true) {
            case !isset($item->type['name']):
            case !\in_array($item->type['name'], ['array', 'ArrayCollection']):
            case !isset($item->type['params'][0]['name']):
            case 'string' != $item->type['params'][0]['name']:
            case !isset($item->type['params'][1]['name']):
                return null;
        }

        return $item->type['params'][1]['name'];
    }
}
