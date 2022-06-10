<?php

namespace Draw\Component\Messenger\Broker\EventListener;

use Draw\Component\Messenger\Broker\Event\NewConsumerProcessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BrokerDefaultValuesListener implements EventSubscriberInterface
{
    private array $defaultOptions;
    private array $receivers;

    public static function getSubscribedEvents(): array
    {
        return [
            NewConsumerProcessEvent::class => ['initializeDefaultValues', 255],
        ];
    }

    public function __construct(array $receivers, array $defaultOptions = [])
    {
        $this->defaultOptions = $defaultOptions;
        $this->receivers = $receivers;
    }

    public function initializeDefaultValues(NewConsumerProcessEvent $event): void
    {
        $event->setReceivers($this->receivers);
        $event->setOptions(
            array_merge(
                $this->defaultOptions,
                $event->getOptions()
            )
        );
    }
}
