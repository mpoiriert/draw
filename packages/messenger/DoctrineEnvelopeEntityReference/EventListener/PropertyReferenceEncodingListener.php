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
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;

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
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();

        if (!$message instanceof DoctrineReferenceAwareInterface) {
            return;
        }

        if ($envelope->last(SentToFailureTransportStamp::class)) {
            // This will prevent removing original stamps from the message
            return;
        }

        $envelope = $envelope->withoutAll(PropertyReferenceStamp::class);

        $stamps = [];

        foreach ($message->getPropertiesWithDoctrineObject() as $propertyName) {
            $object = ReflectionAccessor::getPropertyValue(
                $message,
                $propertyName
            );

            if (!$object) {
                continue;
            }

            ReflectionAccessor::setPropertyValue(
                $message,
                $propertyName,
                null
            );

            $metadata = $this->managerRegistry
                ->getManagerForClass($object::class)
                ->getClassMetadata($object::class);

            $stamps[] = new PropertyReferenceStamp(
                $propertyName,
                $metadata->getName(),
                $metadata->getIdentifierValues($object)
            );
        }

        $event->setEnvelope($envelope->with(...$stamps));
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
