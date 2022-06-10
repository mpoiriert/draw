<?php

namespace Draw\Component\Messenger\AutoStamp\EventListener;

use Draw\Component\Messenger\AutoStamp\Message\StampingAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

class AutoStampEnvelopeListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            SendMessageToTransportsEvent::class => [
                ['handleStampingAwareMessage'],
            ],
        ];
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
