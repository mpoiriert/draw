<?php

namespace Draw\Component\Console\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CommandErrorEvent extends Event
{
    private string $executionId;

    private string $outputString;

    private ?string $autoAcknowledgeReason = null;

    public function __construct(string $executionId, string $outputString)
    {
        $this->executionId = $executionId;
        $this->outputString = $outputString;
    }

    public function getExecutionId(): string
    {
        return $this->executionId;
    }

    public function getOutputString(): string
    {
        return $this->outputString;
    }

    public function isAutoAcknowledge(): bool
    {
        return (bool) $this->autoAcknowledgeReason;
    }

    public function acknowledge(string $reason): self
    {
        $this->autoAcknowledgeReason = $reason;

        $this->stopPropagation();

        return $this;
    }

    public function getAutoAcknowledgeReason(): ?string
    {
        return $this->autoAcknowledgeReason;
    }
}
