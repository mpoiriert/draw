<?php

namespace Draw\Component\OpenApi\Serializer\Handler;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;

class GenericSerializerHandler implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            [
                'event' => Events::PRE_SERIALIZE,
                'method' => 'onPreSerialize',
                'format' => 'json',
            ],
        ];
    }

    public function onPreSerialize(PreSerializeEvent $event): void
    {
        if ('generic' != $event->getType()['name']) {
            return;
        }

        $event->setType(\get_class($event->getObject()));
    }
}
