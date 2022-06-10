<?php

namespace Draw\Component\Messenger\DoctrineMessageBusHook\EnvelopeFactory;

use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Event\EnvelopeCreatedEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class BasicEnvelopeFactory implements EnvelopeFactoryInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createEnvelopes(MessageHolderInterface $messageHolder, array $messages): array
    {
        $envelopes = [];
        foreach ($messages as $message) {
            $envelopes[] = $this->eventDispatcher
                ->dispatch(new EnvelopeCreatedEvent($messageHolder, new Envelope($message)))
                ->getEnvelope();
        }

        return $envelopes;
    }
}
