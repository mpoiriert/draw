<?php

namespace Draw\Component\Messenger\ManualTrigger\EventListener;

use Draw\Component\Messenger\ManualTrigger\Message\ManuallyTriggeredInterface;
use Draw\Component\Messenger\ManualTrigger\Stamp\ManualTriggerStamp;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

class StampManuallyTriggeredEnvelopeListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            SendMessageToTransportsEvent::class => [
                ['handleManuallyTriggeredMessage'],
            ],
        ];
    }

    public function handleManuallyTriggeredMessage(SendMessageToTransportsEvent $event): void
    {
        $envelope = $event->getEnvelope();

        switch (true) {
            case !$envelope->getMessage() instanceof ManuallyTriggeredInterface:
            case $envelope->last(ManualTriggerStamp::class):
                return;
            default:
                $event->setEnvelope($envelope->with(new ManualTriggerStamp()));
                break;
        }
    }
}
