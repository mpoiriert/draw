<?php

declare(strict_types=1);

namespace Draw\Component\DataSynchronizer\Serializer\Construction;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Draw\Component\DataSynchronizer\Metadata\EntitySynchronizationMetadataFactory;
use JMS\Serializer\Metadata\ClassMetadata;

#[AsDoctrineListener(Events::postFlush)]
class MissingReferenceRegistry
{
    private array $objects = [];

    public function __construct(
        private EntitySynchronizationMetadataFactory $importMetadataReader,
    ) {
    }

    public function register(ClassMetadata $metadata, array $data, object $object): void
    {
        $identifier = $this->getIdentifier($metadata, $data);

        $this->objects[$metadata->name][serialize($identifier)] = $object;
    }

    public function find(ClassMetadata $metadata, array $data): ?object
    {
        $identifier = $this->getIdentifier($metadata, $data);

        return $this->objects[$metadata->name][serialize($identifier)] ?? null;
    }

    private function getIdentifier(ClassMetadata $metadata, array $data): array
    {
        $lookUpFields = $this->importMetadataReader
            ->getEntitySynchronizationMetadata($metadata->name)
            ->lookUpFields
        ;

        $identifier = [];
        foreach ($lookUpFields as $field) {
            $identifier[$field] = $data[$field];
        }

        return $identifier;
    }

    public function postFlush(): void
    {
        $this->objects = [];
    }
}
