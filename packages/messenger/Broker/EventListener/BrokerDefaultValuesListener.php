<?php

namespace Draw\Component\Messenger\Broker\EventListener;

use Draw\Component\Messenger\Broker\Event\NewConsumerProcessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BrokerDefaultValuesListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            NewConsumerProcessEvent::class => ['initializeDefaultValues', 255],
        ];
    }

    public function __construct(private array $contexts)
    {
    }

    public function initializeDefaultValues(NewConsumerProcessEvent $event): void
    {
        $event->setReceivers($this->contexts[$event->getContext()]['receivers']);
        $event->setOptions(
            array_merge(
                $this->contexts[$event->getContext()]['defaultOptions'] ?? [],
                $event->getOptions()
            )
        );
    }
}
