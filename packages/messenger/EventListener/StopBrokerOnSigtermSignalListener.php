<?php

namespace Draw\Component\Messenger\EventListener;

use Draw\Component\Messenger\Event\BrokerStartedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StopBrokerOnSigtermSignalListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        $result = [];
        if (\function_exists('pcntl_signal')) {
            $result[BrokerStartedEvent::class] = ['onBrokerStarted', 100];
        }

        return $result;
    }

    public function onBrokerStarted(BrokerStartedEvent $event): void
    {
        $broker = $event->getBroker();
        pcntl_signal(\SIGTERM, [$broker, 'stop']);
    }
}
