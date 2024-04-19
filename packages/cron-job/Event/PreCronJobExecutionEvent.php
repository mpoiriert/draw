<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Event;

use Draw\Component\CronJob\Entity\CronJobExecution;
use Symfony\Contracts\EventDispatcher\Event;

class PreCronJobExecutionEvent extends Event
{
    private string $command;

    public function __construct(
        private CronJobExecution $execution,
        private bool $executionCancelled = false,
    ) {
        $this->command = $this->execution->getCronJob()->getCommand();
    }

    public function getExecution(): CronJobExecution
    {
        return $this->execution;
    }

    public function isExecutionCancelled(): bool
    {
        return $this->executionCancelled;
    }

    public function setExecutionCancelled(bool $executionCancelled): self
    {
        $this->executionCancelled = $executionCancelled;

        return $this;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }
}
