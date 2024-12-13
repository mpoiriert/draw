<?php

namespace Draw\Component\EntityMigrator\Message;

use Draw\Component\EntityMigrator\Entity\EntityMigrationInterface;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Exception\ObjectNotFoundException;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Message\DoctrineReferenceAwareInterface;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Stamp\PropertyReferenceStamp;

class MigrateEntityCommand implements DoctrineReferenceAwareInterface
{
    private PropertyReferenceStamp|EntityMigrationInterface|null $entity;

    public function __construct(EntityMigrationInterface $entity)
    {
        $this->entity = $entity;
    }

    public function getEntity(): EntityMigrationInterface
    {
        if (!$this->entity instanceof EntityMigrationInterface) {
            throw new ObjectNotFoundException($this->entity?->getClass() ?? EntityMigrationInterface::class, $this->entity);
        }

        return $this->entity;
    }

    public function getPropertiesWithDoctrineObject(): array
    {
        return ['entity'];
    }
}
