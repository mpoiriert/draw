<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\Metadata\PropertyMetadata;

class EnumHandler implements TypeToSchemaHandlerInterface
{
    public function extractSchemaFromType(
        PropertyMetadata $propertyMetadata,
        ExtractionContextInterface $extractionContext,
    ): ?Schema {
        if (null === ($type = $this->getEnumClassName($propertyMetadata))) {
            return null;
        }

        $backingType = $type->getBackingType();

        $prop = new Schema();
        $prop->type = $backingType instanceof \ReflectionNamedType && 'int' === $backingType->getName()
            ? 'integer'
            : 'string';

        $prop->enum = array_map(
            static function (\ReflectionEnumBackedCase|\ReflectionEnumUnitCase $value) {
                return $value instanceof \ReflectionEnumBackedCase
                    ? $value->getBackingValue()
                    : $value->getName();
            },
            $type->getCases()
        );

        return $prop;
    }

    private function getEnumClassName(PropertyMetadata $item): ?\ReflectionEnum
    {
        if (!isset($item->type['name'])
            || 'enum' !== $item->type['name']
            || !isset($item->type['params'][0])
            || !enum_exists($item->type['params'][0])
        ) {
            return null;
        }

        return new \ReflectionEnum($item->type['params'][0]);
    }
}
