<?php

namespace Draw\Component\Messenger\Retry\Event;

use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\EventDispatcher\Event;

class GetWaitingTimeEvent extends Event
{
    private ?int $waitingTime = null;

    public function __construct(private Envelope $envelope)
    {
    }

    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }

    public function getWaitingTime(): ?int
    {
        return $this->waitingTime;
    }

    public function setWaitingTime(int $waitingTime): void
    {
        $this->waitingTime = $waitingTime;

        $this->stopPropagation();
    }
}
