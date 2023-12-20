<?php

namespace Draw\Component\Messenger\Retry\Event;

use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\EventDispatcher\Event;

class IsRetryableEvent extends Event
{
    private ?bool $isRetryable = null;

    public function __construct(private Envelope $envelope, private ?\Throwable $throwable = null)
    {
    }

    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }

    public function getThrowable(): ?\Throwable
    {
        return $this->throwable;
    }

    public function getIsRetryable(): ?bool
    {
        return $this->isRetryable;
    }

    public function setIsRetryable(bool $isRetryable): void
    {
        $this->isRetryable = $isRetryable;

        $this->stopPropagation();
    }
}
