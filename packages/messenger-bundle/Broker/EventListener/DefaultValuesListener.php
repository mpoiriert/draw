<?php

namespace Draw\Bundle\MessengerBundle\Broker\EventListener;

use Draw\Bundle\MessengerBundle\Broker\Event\NewConsumerProcessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DefaultValuesListener implements EventSubscriberInterface
{
    private $defaultOptions;
    private $receivers;

    public static function getSubscribedEvents()
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
