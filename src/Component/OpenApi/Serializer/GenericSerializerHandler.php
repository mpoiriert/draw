<?php namespace Draw\Component\OpenApi\Serializer;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;

class GenericSerializerHandler implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::PRE_SERIALIZE,
                'method' => 'onPreSerialize',
                'format' => 'json'
            ]
        ];
    }

    public function onPreSerialize(PreSerializeEvent $event): void
    {
        if ($event->getType()['name'] != 'generic') {
            return;
        }

        $event->setType(get_class($event->getObject()));
    }
}