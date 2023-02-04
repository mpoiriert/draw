<?php

namespace Draw\Component\Messenger\DoctrineEnvelopeEntityReference\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Message\DoctrineReferenceAwareInterface;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Stamp\PropertyReferenceStamp;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\BaseSerializerEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PostDecodeEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PostEncodeEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PreEncodeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PropertyReferenceEncodingListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PreEncodeEvent::class => 'createPropertyReferenceStamps',
            PostEncodeEvent::class => 'restoreDoctrineObjects',
            PostDecodeEvent::class => 'restoreDoctrineObjects',
        ];
    }

    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    public function createPropertyReferenceStamps(PreEncodeEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();

        if (!$message instanceof DoctrineReferenceAwareInterface) {
            return;
        }

        $stamps = [];

        foreach ($message->getPropertiesWithDoctrineObject() as $propertyName) {
            $object = ReflectionAccessor::getPropertyValue(
                $message,
                $propertyName
            );

            ReflectionAccessor::setPropertyValue(
                $message,
                $propertyName,
                null
            );

            $metadata = $this->managerRegistry
                ->getManagerForClass($object::class)
                ->getClassMetadata($object::class);

            $metadata->getName();
            $metadata->getIdentifierValues($object);

            $stamps[] = new PropertyReferenceStamp(
                $propertyName,
                $metadata->getName(),
                $metadata->getIdentifierValues($object)
            );
        }

        $envelope = $event->getEnvelope()->with(...$stamps);

        $event->setEnvelope($envelope);
    }

    public function restoreDoctrineObjects(BaseSerializerEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();

        if (!$message instanceof DoctrineReferenceAwareInterface) {
            return;
        }

        $stamps = $event->getEnvelope()->all(PropertyReferenceStamp::class);

        foreach ($stamps as $stamp) {
            ReflectionAccessor::setPropertyValue(
                $message,
                $stamp->getPropertyName(),
                $this->managerRegistry
                    ->getManagerForClass($stamp->getClass())
                    ->find($stamp->getClass(), $stamp->getIdentifiers())
            );
        }
    }
}
