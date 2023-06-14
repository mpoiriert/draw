<?php

namespace Draw\Component\OpenApi\Serializer\Subscriber;

use Draw\Component\OpenApi\Schema\BaseParameter;
use Draw\Component\OpenApi\Schema\VendorExtensionSupportInterface;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\StaticPropertyMetadata;

class OpenApiSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ['event' => Events::PRE_SERIALIZE, 'method' => 'onPreSerialize'],
            ['event' => Events::PRE_DESERIALIZE, 'method' => 'onPreDeserialize'],
            ['event' => Events::POST_SERIALIZE, 'method' => 'onPostSerialize'],
        ];
    }

    public function onPreSerialize(PreSerializeEvent $event): void
    {
        $object = $event->getObject();
        if (
            \is_object($object)
            && is_subclass_of($object, BaseParameter::class)
            && $object::class !== $event->getType()['name']
        ) {
            $event->setType($event->getObject()::class);
        }
    }

    public function onPreDeserialize(PreDeserializeEvent $event): void
    {
        $data = $event->getData();

        $type = $event->getType();

        if (!class_exists($type['name'])) {
            return;
        }

        if (!\is_array($data)) {
            return;
        }

        $reflectionClass = new \ReflectionClass($type['name']);
        if (!$reflectionClass->implementsInterface(VendorExtensionSupportInterface::class)) {
            return;
        }

        $vendorData = [];

        foreach ($data as $key => $value) {
            if (!\is_string($key)) {
                continue;
            }

            if (!str_starts_with($key, 'x-')) {
                continue;
            }

            unset($data[$key]);
            $vendorData[$key] = $value;
        }

        $data['vendor'] = $vendorData;
        $event->setData($data);
    }

    public function onPostSerialize(ObjectEvent $event): void
    {
        $object = $event->getObject();

        if (!$object instanceof VendorExtensionSupportInterface) {
            return;
        }

        $visitor = $event->getVisitor();

        if (!$visitor instanceof JsonSerializationVisitor) {
            return;
        }

        foreach ($object->getVendorData() as $key => $value) {
            $visitor->visitProperty(new StaticPropertyMetadata('', $key, $value), $value);
        }
    }
}
