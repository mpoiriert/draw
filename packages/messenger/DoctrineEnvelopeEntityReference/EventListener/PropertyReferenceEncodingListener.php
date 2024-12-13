<?php

namespace Draw\Component\Messenger\DoctrineEnvelopeEntityReference\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Core\Reflection\ReflectionExtractor;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Message\DoctrineReferenceAwareInterface;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Stamp\PropertyReferenceStamp;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\BaseSerializerEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PostDecodeEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PostEncodeEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PreEncodeEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;

class PropertyReferenceEncodingListener
{
    public function __construct(
        private ?ManagerRegistry $ormManagerRegistry,
        private ?ManagerRegistry $odmManagerRegistry,
    ) {
    }

    #[AsEventListener]
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

            $metadata = $this->getManagerForClass($object::class)
                ->getClassMetadata($object::class)
            ;

            $stamps[] = new PropertyReferenceStamp(
                $propertyName,
                $metadata->getName(),
                $metadata->getIdentifierValues($object)
            );
        }

        $event->setEnvelope($envelope->with(...$stamps));
    }

    #[
        AsEventListener(event: PostEncodeEvent::class),
        AsEventListener(event: PostDecodeEvent::class)
    ]
    public function restoreDoctrineObjects(BaseSerializerEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();

        if (!$message instanceof DoctrineReferenceAwareInterface) {
            return;
        }

        $stamps = $event->getEnvelope()->all(PropertyReferenceStamp::class);

        foreach ($stamps as $stamp) {
            $object = $this->getManagerForClass($stamp->getClass())
                ->find($stamp->getClass(), $stamp->getIdentifiers())
            ;

            if ($object) {
                ReflectionAccessor::setPropertyValue(
                    $message,
                    $stamp->getPropertyName(),
                    $object
                );

                continue;
            }

            $propertyReflection = ReflectionAccessor::getPropertyReflection(
                $message,
                $stamp->getPropertyName(),
            );

            $classes = ReflectionExtractor::getClasses($propertyReflection->getType());

            if (!\in_array(PropertyReferenceStamp::class, $classes, true)) {
                continue;
            }

            ReflectionAccessor::setPropertyValue(
                $message,
                $stamp->getPropertyName(),
                $stamp,
            );
        }
    }

    private function getManagerForClass(string $class): ObjectManager
    {
        $objectManager =
            $this->ormManagerRegistry?->getManagerForClass($class)
            ?? $this->odmManagerRegistry?->getManagerForClass($class);

        if (!$objectManager) {
            throw new \RuntimeException(\sprintf('No manager found for class "%s"', $class));
        }

        return $objectManager;
    }
}
