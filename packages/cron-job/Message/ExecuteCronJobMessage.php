<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Message;

use Draw\Component\CronJob\Entity\CronJobExecution;
use Draw\Component\Messenger\AutoStamp\Message\StampingAwareInterface;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Exception\ObjectNotFoundException;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Message\DoctrineReferenceAwareInterface;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Stamp\PropertyReferenceStamp;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;

class ExecuteCronJobMessage implements DoctrineReferenceAwareInterface, StampingAwareInterface
{
    private PropertyReferenceStamp|CronJobExecution|null $execution;

    public function __construct(CronJobExecution $execution)
    {
        $this->execution = $execution;
    }

    public function getExecution(): CronJobExecution
    {
        if (!$this->execution instanceof CronJobExecution) {
            throw new ObjectNotFoundException(CronJobExecution::class, $this->execution);
        }

        return $this->execution;
    }

    public function getPropertiesWithDoctrineObject(): array
    {
        return ['execution'];
    }

    public function stamp(Envelope $envelope): Envelope
    {
        if (null !== ($priority = $this->execution?->getCronJob()?->getPriority())) {
            return $envelope->with(
                AmqpStamp::createWithAttributes(['priority' => $priority])
            );
        }

        return $envelope;
    }
}
