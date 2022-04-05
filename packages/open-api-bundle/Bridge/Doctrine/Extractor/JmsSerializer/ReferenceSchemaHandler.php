<?php

namespace Draw\Bundle\OpenApiBundle\Bridge\Doctrine\Extractor\JmsSerializer;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\TypeToSchemaHandlerInterface;
use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\Metadata\PropertyMetadata;
use Metadata\MetadataFactoryInterface;

class ReferenceSchemaHandler implements TypeToSchemaHandlerInterface
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    public function __construct(ManagerRegistry $managerRegistry, MetadataFactoryInterface $factory)
    {
        $this->managerRegistry = $managerRegistry;
        $this->metadataFactory = $factory;
    }

    public function extractSchemaFromType(
        PropertyMetadata $propertyMetadata,
        ExtractionContextInterface $extractionContext
    ) {
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
            case 'ObjectReference' != $item->type['name']:
            case !isset($item->type['params'][0]['name']):
                return null;
        }

        $class = $item->type['params'][0]['name'];

        $metadataFor = $this->managerRegistry->getManagerForClass($class)
            ->getMetadataFactory()
            ->getMetadataFor($class);

        return $this->getTypeFromJsmFactory(
            $class,
            $metadataFor->getIdentifierFieldNames()[0]
        );
    }

    private function getTypeFromJsmFactory(string $class, string $propertyId): ?array
    {
        switch (true) {
            case null === $classMetadata = $this->metadataFactory->getMetadataForClass($class):
            case null === $propertyMetadata = $classMetadata->propertyMetadata[$propertyId] ?? null:
                return null;
        }

        $type = $propertyMetadata->type['name'];

        return TypeSchemaExtractor::getPrimitiveType($type);
    }
}
