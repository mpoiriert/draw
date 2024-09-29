<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\Metadata\PropertyMetadata;
use Metadata\MetadataFactoryInterface;

class DoctrineObjectReferenceSchemaHandler implements TypeToSchemaHandlerInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private MetadataFactoryInterface $metadataFactory,
    ) {
    }

    public function extractSchemaFromType(
        PropertyMetadata $propertyMetadata,
        ExtractionContextInterface $extractionContext,
    ): ?Schema {
        if (null === ($type = $this->getReferenceType($propertyMetadata))) {
            return null;
        }

        $propertySchema = new Schema();
        $propertySchema->type = $type['type'];
        $propertySchema->format = $type['format'] ?? null;

        return $propertySchema;
    }

    private function getReferenceType(PropertyMetadata $item): ?array
    {
        switch (true) {
            case !isset($item->type['name']):
            case 'ObjectReference' !== $item->type['name']:
            case !isset($item->type['params'][0]['name']):
                return null;
        }

        $class = $item->type['params'][0]['name'];

        $metadataFor = $this->managerRegistry->getManagerForClass($class)
            ->getMetadataFactory()
            ->getMetadataFor($class)
        ;

        return $this->getTypeFromJsmFactory(
            $class,
            $metadataFor->getIdentifierFieldNames()[0]
        );
    }

    private function getTypeFromJsmFactory(string $class, string $propertyId): ?array
    {
        if (!$classMetadata = $this->metadataFactory->getMetadataForClass($class)) {
            return null;
        }

        if (null === $propertyMetadata = $classMetadata->propertyMetadata[$propertyId] ?? null) {
            return null;
        }

        return TypeSchemaExtractor::getPrimitiveType($propertyMetadata->type['name'] ?? null);
    }
}
