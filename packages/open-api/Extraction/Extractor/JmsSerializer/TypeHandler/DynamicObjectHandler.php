<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\Metadata\PropertyMetadata;

class DynamicObjectHandler implements TypeToSchemaHandlerInterface
{
    public function extractSchemaFromType(
        PropertyMetadata $propertyMetadata,
        ExtractionContextInterface $extractionContext,
    ): ?Schema {
        if (!($type = $this->getDynamicObjectType($propertyMetadata))) {
            return null;
        }

        if ('mixed' === $type) {
            $propertySchema = true;
        } else {
            $extractionContext->getOpenApi()
                ->extract(
                    $type,
                    $propertySchema = new Schema(),
                    $extractionContext
                )
            ;
        }

        $schema = new Schema();
        $schema->type = 'object';
        $schema->additionalProperties = $propertySchema;

        return $schema;
    }

    private function getDynamicObjectType(PropertyMetadata $item): ?string
    {
        return match (true) {
            !isset($item->type['name']), !\in_array($item->type['name'], ['array', 'ArrayCollection'], true), !isset($item->type['params'][0]['name']), 'string' !== $item->type['params'][0]['name'], !isset($item->type['params'][1]['name']) => null,
            default => $item->type['params'][1]['name'],
        };
    }
}
