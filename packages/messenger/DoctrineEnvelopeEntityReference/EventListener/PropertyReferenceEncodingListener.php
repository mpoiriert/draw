<?php

namespace Draw\Component\Messenger\DoctrineEnvelopeEntityReference\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Message\DoctrineReferenceAwareInterface;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Stamp\PropertyReferenceStamp;
use Draw\Component\Messenger\Transport\Event\BaseSerializerEvent;
use Draw\Component\Messenger\Transport\Event\PostDecodeEvent;
use Draw\Component\Messenger\Transport\Event\PostEncodeEvent;
use Draw\Component\Messenger\Transport\Event\PreEncodeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PropertyReferenceEncodingListener implements EventSubscriberInterface
{
    private ManagerRegistry $managerRegistry;

    public static function getSubscribedEvents(): array
    {
        return [
            PreEncodeEvent::class => 'createPropertyReferenceStamps',
            PostEncodeEvent::class => 'restoreDoctrineObjects',
            PostDecodeEvent::class => 'restoreDoctrineObjects',
        ];
    }

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function createPropertyReferenceStamps(PreEncodeEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();

        if (!$message instanceof DoctrineReferenceAwareInterface) {
            return;
        }

        $stamps = [];

        foreach ($message->getDoctrineObjects() as $key => $object) {
            $metadata = $this->managerRegistry
                ->getManagerForClass(\get_class($object))
                ->getClassMetadata(\get_class($object));

            $metadata->getName();
            $metadata->getIdentifierValues($object);

            $stamps[] = new PropertyReferenceStamp(
                $key,
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

        $doctrineObjects = [];

        foreach ($stamps as $stamp) {
            $doctrineObjects[$stamp->getKey()] = $this->managerRegistry
                ->getManagerForClass($stamp->getClass())
                ->find($stamp->getClass(), $stamp->getIdentifiers());
        }

        $message->restoreDoctrineObjects($doctrineObjects);
    }
}
