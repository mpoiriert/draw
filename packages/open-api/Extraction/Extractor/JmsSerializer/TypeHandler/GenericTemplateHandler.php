<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\Metadata\PropertyMetadata;

class GenericTemplateHandler implements TypeToSchemaHandlerInterface
{
    public function extractSchemaFromType(
        PropertyMetadata $propertyMetadata,
        ExtractionContextInterface $extractionContext,
    ): ?Schema {
        if ($genericType = $this->getGenericType($propertyMetadata)) {
            $extractionContext->getOpenApi()
                ->extract(
                    $propertyMetadata->type['name'].'<'.$genericType.'>',
                    $propertySchema = new Schema(),
                    $extractionContext
                )
            ;

            return $propertySchema;
        }

        if (($propertyMetadata->type['name'] ?? null) !== 'generic') {
            return null;
        }

        $genericTemplate = $extractionContext->getParameter('generic-template');

        if (!$genericTemplate) {
            return null;
        }

        $extractionContext->getOpenApi()
            ->extract(
                $genericTemplate,
                $propertySchema = new Schema(),
                $extractionContext
            )
        ;

        return $propertySchema;
    }

    private function getGenericType(PropertyMetadata $item): ?string
    {
        return match (true) {
            !isset($item->type['name']), \in_array($item->type['name'], ['array', 'ArrayCollection'], true), !class_exists($item->type['name']), !isset($item->type['params'][0]['name']), isset($item->type['params'][1]['name']) => null,
            default => $item->type['params'][0]['name'],
        };
    }
}
