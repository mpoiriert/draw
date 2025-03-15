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
        return $this->createSchemaFromType($propertyMetadata->type);
    }

    public function createSchemaFromType(array $type): ?Schema
    {
        if (null === ($result = $this->getEnumClassName($type))) {
            return null;
        }

        $type = $result[0];
        $enumType = $result[1];

        $backingType = $type->getBackingType();

        $prop = new Schema();

        if ('value' === $enumType || null === $enumType) {
            $prop->type = $backingType instanceof \ReflectionNamedType && 'int' === $backingType->getName()
                ? 'integer'
                : 'string';
        } else {
            $prop->type = 'string';
        }

        $prop->enum = array_map(
            static function (\ReflectionEnumBackedCase|\ReflectionEnumUnitCase $value) use ($type, $enumType): string|int {
                if ('name' === $enumType) {
                    return $value->getName();
                }

                if ('value' === $enumType) {
                    if (!$value instanceof \ReflectionEnumBackedCase) {
                        throw new \RuntimeException(\sprintf('Enum [%s] value is not backed you should use [name] instead of [value]', $type->getName()));
                    }

                    return $value->getBackingValue();
                }

                return $value instanceof \ReflectionEnumBackedCase
                    ? $value->getBackingValue()
                    : $value->getName();
            },
            $type->getCases()
        );

        return $prop;
    }

    /**
     * @return array{0: \ReflectionEnum, 1: string}|null
     */
    private function getEnumClassName(?array $type): ?array
    {
        if (!isset($type['name'])
            || 'enum' !== $type['name']
            || !isset($type['params'][0]['name'])
            || !enum_exists($type['params'][0]['name'])
        ) {
            return null;
        }

        return [new \ReflectionEnum($type['params'][0]['name']), $type['params'][1] ?? null];
    }
}
