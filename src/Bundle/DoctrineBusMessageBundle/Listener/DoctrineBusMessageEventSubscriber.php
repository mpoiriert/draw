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
    private $enveloperFactory;

    public function __construct(MessageBusInterface $bus, EnvelopeFactoryInterface $envelopeFactory = null)
    {
        $this->bus = $bus;
        $this->enveloperFactory = $envelopeFactory ?: new BasicEnvelopeFactory();
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

        foreach ($entities as $entity) {
            if (!$entity instanceof MessageHolderInterface) {
                continue;
            }

            $queue = $entity->messageQueue();
            while (!$queue->isEmpty()) {
                $message = $queue->dequeue();
                if (null === $envelope = $this->enveloperFactory->createEnvelope($message)) {
                    continue;
                }
                $this->bus->dispatch($envelope);
            }
        }
    }
}
