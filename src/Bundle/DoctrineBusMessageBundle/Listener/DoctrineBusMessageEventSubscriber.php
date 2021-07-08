<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Draw\Bundle\DoctrineBusMessageBundle\EnvelopeFactory\BasicEnvelopeFactory;
use Draw\Bundle\DoctrineBusMessageBundle\EnvelopeFactory\EnvelopeFactoryInterface;
use Draw\Bundle\DoctrineBusMessageBundle\MessageHolderInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class DoctrineBusMessageEventSubscriber implements EventSubscriber
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var EnvelopeFactoryInterface
     */
    private $envelopeFactory;

    public function __construct(MessageBusInterface $bus, EnvelopeFactoryInterface $envelopeFactory = null)
    {
        $this->bus = $bus;
        $this->envelopeFactory = $envelopeFactory ?: new BasicEnvelopeFactory();
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postFlush,
        ];
    }

    public function postFlush(PostFlushEventArgs $event): void
    {
        $identityMap = $event->getEntityManager()->getUnitOfWork()->getIdentityMap();
        if (!$identityMap) {
            return;
        }

        $entities = call_user_func_array('array_merge', $identityMap);

        $envelopes = [];
        foreach ($entities as $entity) {
            if (!$entity instanceof MessageHolderInterface) {
                continue;
            }

            $queue = $entity->messageQueue();

            if ($queue->isEmpty()) {
                continue;
            }

            $messages = [];
            while (!$queue->isEmpty()) {
                $messages[] = $queue->dequeue();
            }

            $envelopes = array_merge($this->envelopeFactory->createEnvelopes($entity, $messages), $envelopes);
        }

        foreach ($envelopes as $envelope) {
            $this->bus->dispatch($envelope);
        }
    }
}
