<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Proxy;
use Draw\Bundle\DoctrineBusMessageBundle\EnvelopeFactory\BasicEnvelopeFactory;
use Draw\Bundle\DoctrineBusMessageBundle\EnvelopeFactory\EnvelopeFactoryInterface;
use Draw\Bundle\DoctrineBusMessageBundle\MessageHolderInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class DoctrineBusMessageEventSubscriber implements EventSubscriber
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MessageHolderInterface[]
     */
    private $messageHolders = [];

    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var EnvelopeFactoryInterface
     */
    private $envelopeFactory;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
        EnvelopeFactoryInterface $envelopeFactory = null
    ) {
        $this->bus = $bus;
        $this->entityManager = $entityManager;
        $this->envelopeFactory = $envelopeFactory ?: new BasicEnvelopeFactory();
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postLoad,
            Events::postFlush,
            Events::onClear,
        ];
    }

    public function postPersist(LifecycleEventArgs $event): void
    {
        $this->trackMessageHolder($event);
    }

    public function postRemove(LifecycleEventArgs $event): void
    {
        $this->trackMessageHolder($event);
    }

    public function postLoad(LifecycleEventArgs $event): void
    {
        $this->trackMessageHolder($event);
    }

    public function onClear(OnClearEventArgs $args): void
    {
        if ($args->clearsAllEntities()) {
            $this->messageHolders = [];

            return;
        }

        $this->messageHolders[$args->getEntityClass()] = [];
    }

    public function postFlush(): void
    {
        $envelopes = [];
        foreach ($this->getFlattenMessageHolders() as $messageHolder) {
            if ($messageHolder instanceof Proxy && !$messageHolder->__isInitialized()) {
                continue;
            }

            $queue = $messageHolder->messageQueue();

            if ($queue->isEmpty()) {
                continue;
            }

            $messages = [];
            while (!$queue->isEmpty()) {
                $messages[] = $queue->dequeue();
            }

            $envelopes = array_merge($this->envelopeFactory->createEnvelopes($messageHolder, $messages), $envelopes);
        }

        foreach ($envelopes as $envelope) {
            $this->bus->dispatch($envelope);
        }
    }

    private function trackMessageHolder(LifecycleEventArgs $event): void
    {
        $entity = $event->getEntity();

        if (!$entity instanceof MessageHolderInterface) {
            return;
        }

        $classMetadata = $this->entityManager->getClassMetadata(get_class($entity));
        $className = $classMetadata->rootEntityName;
        $this->messageHolders[$className][spl_object_id($entity)] = $entity;
    }

    /**
     * @return array|MessageHolderInterface[]
     *
     * @internal
     */
    public function getFlattenMessageHolders(): array
    {
        if (!$this->messageHolders) {
            return [];
        }

        return call_user_func_array('array_merge', $this->messageHolders);
    }
}
