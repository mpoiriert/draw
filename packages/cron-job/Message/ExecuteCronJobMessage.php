<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Message;

use Draw\Component\CronJob\Entity\CronJobExecution;
use Draw\Component\Messenger\AutoStamp\Message\StampingAwareInterface;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Message\DoctrineReferenceAwareInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class ExecuteCronJobMessage implements DoctrineReferenceAwareInterface, StampingAwareInterface
{
    private ?CronJobExecution $execution;

    public function __construct(CronJobExecution $execution)
    {
        $this->execution = $execution;
    }

    public function getExecution(): CronJobExecution
    {
        if (null === $this->execution) {
            throw new UnrecoverableMessageHandlingException('CronJobExecution is not set.');
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
