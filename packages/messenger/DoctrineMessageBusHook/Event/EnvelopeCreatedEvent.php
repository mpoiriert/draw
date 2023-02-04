<?php

namespace Draw\Component\Messenger\DoctrineMessageBusHook\Event;

use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\EventDispatcher\Event;

class EnvelopeCreatedEvent extends Event
{
    public function __construct(private MessageHolderInterface $messageHolder, private Envelope $envelope)
    {
    }

    public function getMessageHolder(): MessageHolderInterface
    {
        return $this->messageHolder;
    }

    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }

    public function setEnvelope(Envelope $envelope): void
    {
        $this->envelope = $envelope;
    }
}
