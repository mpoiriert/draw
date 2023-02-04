<?php

namespace Draw\Component\Messenger\DoctrineMessageBusHook\EventListener;

use Draw\Component\Messenger\DoctrineMessageBusHook\Event\EnvelopeCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class EnvelopeFactoryDelayStampListener implements EventSubscriberInterface
{
    public function __construct(private int $delay)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EnvelopeCreatedEvent::class => 'handleEnvelopeCreatedEvent',
        ];
    }

    public function handleEnvelopeCreatedEvent(EnvelopeCreatedEvent $event): void
    {
        $envelope = $event->getEnvelope();

        if ($envelope->last(DelayStamp::class)) {
            return;
        }

        $event
            ->setEnvelope(
                $envelope->with(new DelayStamp($this->delay))
            );
    }
}
