<?php

namespace Draw\Component\Console\Event;

use Symfony\Component\Console\Event\ConsoleEvent;

class LoadExecutionIdEvent extends ConsoleEvent
{
    private ?int $executionId = null;

    private bool $ignoreTracking = false;

    public function getExecutionId(): ?int
    {
        return $this->executionId;
    }

    public function setExecutionId(int $executionId): self
    {
        $this->executionId = $executionId;

        $this->stopPropagation();

        return $this;
    }

    public function getIgnoreTracking(): bool
    {
        return $this->ignoreTracking;
    }

    public function ignoreTracking(): self
    {
        $this->ignoreTracking = true;

        $this->stopPropagation();

        return $this;
    }
}
