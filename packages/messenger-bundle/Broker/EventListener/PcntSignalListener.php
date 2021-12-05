<?php

namespace Draw\Bundle\MessengerBundle\Broker\EventListener;

use Draw\Bundle\MessengerBundle\Broker\Event\BrokerRunningEvent;
use Draw\Bundle\MessengerBundle\Broker\Event\BrokerStartedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PcntSignalListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        if (!\function_exists('pcntl_signal') || !\function_exists('pcntl_signal_dispatch')) {
            return [];
        }

        return [
            BrokerStartedEvent::class => [
                'onBrokerStarted',
            ],
            BrokerRunningEvent::class => [
                'onBrokerRunning',
            ],
        ];
    }

    public function onBrokerStarted(BrokerStartedEvent $event): void
    {
        pcntl_signal(SIGTERM, static function () use ($event) {
            $event->getBroker()->stop(false);
        });

        pcntl_signal(SIGINT, static function () use ($event) {
            $event->getBroker()->stop(false);
        });
    }

    public function onBrokerRunning(): void
    {
        pcntl_signal_dispatch();
    }
}
