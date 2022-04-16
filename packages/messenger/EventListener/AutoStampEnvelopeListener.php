<?php

namespace Draw\Component\Messenger\EventListener;

use Draw\Component\Messenger\Message\ManuallyTriggeredInterface;
use Draw\Component\Messenger\Message\StampingAwareInterface;
use Draw\Component\Messenger\Stamp\ManualTriggerStamp;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

class AutoStampEnvelopeListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            SendMessageToTransportsEvent::class => [
                ['handleManuallyTriggeredMessage'],
                ['handleStampingAwareMessage'],
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

    public function handleStampingAwareMessage(SendMessageToTransportsEvent $event): void
    {
        $envelope = $event->getEnvelope();

        switch (true) {
            case null === $message = $envelope->getMessage():
            case !$message instanceof StampingAwareInterface:
                return;
            default:
                $event->setEnvelope($message->stamp($envelope));
                break;
        }
    }
}
