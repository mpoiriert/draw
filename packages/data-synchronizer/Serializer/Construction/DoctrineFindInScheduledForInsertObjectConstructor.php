<?php

declare(strict_types=1);

namespace Draw\Component\DataSynchronizer\Serializer\Construction;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\DataSynchronizer\Metadata\EntitySynchronizationMetadataFactory;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

class DoctrineFindInScheduledForInsertObjectConstructor implements ObjectConstructorInterface
{
    public function __construct(
        #[AutowireDecorated]
        private ObjectConstructorInterface $fallbackConstructor,
        private EntitySynchronizationMetadataFactory $importMetadataReader,
        private ManagerRegistry $doctrine,
    ) {
    }

    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context,
    ): ?object {
        $lookUpFields = $this->importMetadataReader
            ->getEntitySynchronizationMetadata($metadata->name)
            ->lookUpFields
        ;

        foreach ($this->getEntitiesOfClass($metadata->name) as $entity) {
            if ($this->entityMatch($lookUpFields, $data, $entity)) {
                return $entity;
            }
        }

        return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
    }

    private function entityMatch(array $lookUpFields, array $data, object $entity): bool
    {
        $matchFound = false;
        foreach ($lookUpFields as $lookUpField) {
            // todo use doctrine metadata to access property value
            if ($entity->{'get'.ucfirst($lookUpField)}() !== $data[$lookUpField]) {
                return false;
            }

            $matchFound = true;
        }

        return $matchFound;
    }

    private function getEntitiesOfClass(string $class): iterable
    {
        $manager = $this->doctrine->getManagerForClass($class);
        \assert($manager instanceof EntityManagerInterface);

        $unitOfWork = $manager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            if ($entity::class === $class) {
                yield $entity;
            }
        }
    }
}
