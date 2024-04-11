<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Message;

use Draw\Component\CronJob\Entity\CronJobExecution;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Message\DoctrineReferenceAwareInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class ExecuteCronJobMessage implements DoctrineReferenceAwareInterface
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
}
