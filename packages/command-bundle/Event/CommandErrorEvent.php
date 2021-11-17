<?php

namespace Draw\Bundle\CommandBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CommandErrorEvent extends Event
{
    private $executionId;

    private $outputString;

    private $autoAcknowledgeReason;

    public function __construct($executionId, string $outputString)
    {
        $this->executionId = $executionId;
        $this->outputString = $outputString;
    }

    public function getExecutionId()
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

    public function acknowledge(string $reason): void
    {
        $this->autoAcknowledgeReason = $reason;
    }

    public function getAutoAcknowledgeReason(): ?string
    {
        return $this->autoAcknowledgeReason;
    }
}
