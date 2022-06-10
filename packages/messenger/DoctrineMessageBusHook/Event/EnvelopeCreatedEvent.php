<?php

namespace Draw\Component\Messenger\DoctrineMessageBusHook\Event;

use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\EventDispatcher\Event;

class EnvelopeCreatedEvent extends Event
{
    private Envelope $envelope;

    private MessageHolderInterface $messageHolder;

    public function __construct(MessageHolderInterface $messageHolder, Envelope $envelope)
    {
        $this->envelope = $envelope;
        $this->messageHolder = $messageHolder;
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
