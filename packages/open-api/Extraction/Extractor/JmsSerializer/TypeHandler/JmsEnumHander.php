<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\Metadata\PropertyMetadata;

class JmsEnumHander implements TypeToSchemaHandlerInterface
{
    public function extractSchemaFromType(
        PropertyMetadata $propertyMetadata,
        ExtractionContextInterface $extractionContext
    ): ?Schema {
        if (null === ($type = $this->getEnumFqcn($propertyMetadata))) {
            return null;
        }

        $prop = new Schema();
        $prop->type = $type->getBackingType() instanceof \ReflectionNamedType
                        && 'int' === $type->getBackingType()->getName() ? 'integer' : 'string';
        $prop->enum = array_map(static fn (\ReflectionEnumBackedCase|\ReflectionEnumUnitCase $value) => $value instanceof \ReflectionEnumBackedCase ? $value->getBackingValue() : $value->getName(), $type->getCases());

        return $prop;
    }

    private function getEnumFqcn(PropertyMetadata $item): ?\ReflectionEnum
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
