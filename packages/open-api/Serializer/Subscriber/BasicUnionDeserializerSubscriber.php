<?php

namespace Draw\Component\OpenApi\Serializer\Subscriber;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;

class BasicUnionDeserializerSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            [
                'event' => Events::PRE_DESERIALIZE,
                'method' => 'onPreDeserialize',
                'format' => 'json',
            ],
        ];
    }

    public function onPreDeserialize(PreDeserializeEvent $event): void
    {
        if ('union' !== $event->getType()['name']) {
            return;
        }

        $data = $event->getData();

        $type = get_debug_type($data);

        $class = null;
        foreach ($event->getType()['params'][0] as $param) {
            if ($type === $param['name']) {
                $event->setType($param['name']);

                return;
            }

            if (!class_exists($param['name'])) {
                continue;
            }

            if ('array' === $type) {
                if (null !== $class) {
                    return; // More than one type matched we ignore it, maybe the built in UnionHandler will handle it later
                }
                $class = $param['name'];
            }
        }

        if (null !== $class) {
            $event->setType($class);
        }
    }
}
