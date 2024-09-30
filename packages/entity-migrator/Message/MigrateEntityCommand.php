<?php

namespace Draw\Component\EntityMigrator\Message;

use Draw\Component\EntityMigrator\Entity\EntityMigrationInterface;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Message\DoctrineReferenceAwareInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class MigrateEntityCommand implements DoctrineReferenceAwareInterface
{
    private ?EntityMigrationInterface $entity;

    public function __construct(EntityMigrationInterface $entity)
    {
        $this->entity = $entity;
    }

    public function getEntity(): ?EntityMigrationInterface
    {
        if (null === $this->entity) {
            throw new UnrecoverableMessageHandlingException('Entity not found');
        }

        return $this->entity;
    }

    public function getPropertiesWithDoctrineObject(): array
    {
        return ['entity'];
    }
}
