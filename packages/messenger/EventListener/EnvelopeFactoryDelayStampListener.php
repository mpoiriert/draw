<?php

namespace Draw\Component\Messenger\EventListener;

use Draw\Component\Messenger\Event\EnvelopeCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class EnvelopeFactoryDelayStampListener implements EventSubscriberInterface
{
    private int $delay;

    public function __construct(int $delay)
    {
        $this->delay = $delay;
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
