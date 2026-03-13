<?php

namespace Draw\Component\DataSynchronizer\Serializer\Construction;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\DataSynchronizer\Metadata\EntitySynchronizationMetadataFactory;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator('draw.data_synchronizer.serializer.construction.default', priority: 60)]
class DoctrineFromLookUpFieldsObjectConstructor implements ObjectConstructorInterface
{
    public function __construct(
        #[AutowireDecorated]
        private ObjectConstructorInterface $fallbackConstructor,
        private EntitySynchronizationMetadataFactory $importMetadataReader,
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context,
    ): ?object {
        $class = $metadata->name;

        $objectManager = $this->managerRegistry->getManagerForClass($class);
        if (!$objectManager) {
            return null;
        }

        $entitySynchronizationMetadata = $this->importMetadataReader->getEntitySynchronizationMetadata($class);

        if (!\is_array($data)) {
            if (\count($entitySynchronizationMetadata->lookUpFields) > 1) {
                throw new \LogicException(\sprintf('Class "%s" has multiple lookup fields, but only one value was provided.', $class));
            }

            $data = [$entitySynchronizationMetadata->lookUpFields[0] => $data];
        }

        $identifierList = [];
        foreach ($entitySynchronizationMetadata->lookUpFields as $field) {
            // Look up field value is required to find the object, if not present, return null
            if (!\array_key_exists($field, $data)) {
                return null;
            }

            $identifierList[$field] = $visitor->visitProperty(
                $metadata->propertyMetadata[$field],
                $data,
            );
        }

        return $objectManager->getRepository($class)->findOneBy($identifierList)
            ?? $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
    }
}
