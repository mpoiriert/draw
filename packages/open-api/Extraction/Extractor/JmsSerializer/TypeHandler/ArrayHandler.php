<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\PropertiesExtractor;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\Metadata\PropertyMetadata;

class ArrayHandler implements TypeToSchemaHandlerInterface
{
    public function __construct(
        private EnumHandler $enumHandler,
    ) {
    }

    public function extractSchemaFromType(
        PropertyMetadata $propertyMetadata,
        ExtractionContextInterface $extractionContext,
    ): ?Schema {
        if (!($type = $this->getNestedTypeInArray($propertyMetadata))) {
            return null;
        }

        $propertySchema = new Schema();
        $propertySchema->type = 'array';
        $propertySchema->items = 'enum' === $type
            ? $this->enumHandler->createSchemaFromType($propertyMetadata->type['params'][0])
            : PropertiesExtractor::extractTypeSchema($type, $extractionContext, $propertyMetadata);

        return $propertySchema;
    }

    private function getNestedTypeInArray(PropertyMetadata $item): ?string
    {
        switch (true) {
            case !isset($item->type['name']):
            case !\in_array($item->type['name'], ['array', 'ArrayCollection'], true):
            case !isset($item->type['params'][0]['name']):
                return null;
        }

        if (isset($item->type['params'][1]['name'])
            && !\in_array($item->type['params'][0]['name'], ['int', 'integer'], true)
        ) {
            return null;
        }

        // E.g. array<integer, MyNamespaceMyObject> or array<MyNamespaceMyObject>
        return $item->type['params'][1]['name'] ?? $item->type['params'][0]['name'];
    }
}
