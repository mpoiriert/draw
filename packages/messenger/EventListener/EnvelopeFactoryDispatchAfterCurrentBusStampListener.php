<?php

namespace Draw\Component\Messenger\EventListener;

use Draw\Component\Messenger\Event\EnvelopeCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

class EnvelopeFactoryDispatchAfterCurrentBusStampListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            EnvelopeCreatedEvent::class => 'handleEnvelopeCreatedEvent',
        ];
    }

    public function handleEnvelopeCreatedEvent(EnvelopeCreatedEvent $event): void
    {
        $event
            ->setEnvelope(
                $event->getEnvelope()->with(new DispatchAfterCurrentBusStamp())
            );
    }
}
