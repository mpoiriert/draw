<?php

namespace Draw\Component\EntityMigrator\Message;

use Draw\Component\EntityMigrator\Entity\EntityMigrationInterface;
use Draw\Component\Messenger\AutoStamp\Message\StampingAwareInterface;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Message\DoctrineReferenceAwareInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class MigrateEntityCommand implements DoctrineReferenceAwareInterface, StampingAwareInterface
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

    public function stamp(Envelope $envelope): Envelope
    {
        return $envelope->with(
            DelayStamp::delayUntil(new \DateTimeImmutable('+1 minute'))
        );
    }
}
