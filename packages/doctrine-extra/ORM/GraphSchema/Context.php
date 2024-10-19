<?php

namespace Draw\DoctrineExtra\ORM\GraphSchema;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class Context
{
    private array $ignoreEntities = [];

    private array $forEntities = [];

    private bool $ignoreAll = false;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private string $name = 'default',
    ) {
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setIgnoreAll(bool $ignoreAll): self
    {
        $this->ignoreAll = $ignoreAll;

        return $this;
    }

    public function getIgnoreAll(): bool
    {
        return $this->ignoreAll;
    }

    public function forEntity(string $entity): self
    {
        if (!\in_array($entity, $this->ignoreEntities, true)) {
            $this->forEntities[] = $entity;
        }

        return $this;
    }

    public function getForEntities(): array
    {
        return $this->forEntities;
    }

    public function forEntityCluster(string $entity, bool $includeReverseRelation = true): self
    {
        $this->forEntities[] = $entity;

        foreach ($this->entityManager->getClassMetadata($entity)->getAssociationMappings() as $associationMapping) {
            $this->forEntity($associationMapping['targetEntity']);
        }

        if ($includeReverseRelation) {
            foreach ($this->entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
                foreach ($metadata->getAssociationMappings() as $associationMapping) {
                    if ($associationMapping['targetEntity'] !== $entity) {
                        continue;
                    }

                    $this->forEntity($associationMapping['sourceEntity']);
                }
            }
        }

        return $this;
    }

    public function ignoreEntity(string $entity): self
    {
        $this->ignoreEntities[] = $entity;

        return $this;
    }

    public function getIgnoreEntities(): array
    {
        return $this->ignoreEntities;
    }
}
