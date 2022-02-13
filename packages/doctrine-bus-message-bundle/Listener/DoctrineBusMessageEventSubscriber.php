<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Proxy;
use Draw\Bundle\DoctrineBusMessageBundle\Entity\MessageHolderInterface;
use Draw\Bundle\DoctrineBusMessageBundle\EnvelopeFactory\BasicEnvelopeFactory;
use Draw\Bundle\DoctrineBusMessageBundle\EnvelopeFactory\EnvelopeFactoryInterface;
use Draw\Bundle\DoctrineBusMessageBundle\Message\LifeCycleAwareMessageInterface;
use Draw\Bundle\DoctrineBusMessageBundle\MessageHolderInterface as DeprecatedMessageHolderInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class DoctrineBusMessageEventSubscriber implements EventSubscriber
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var DeprecatedMessageHolderInterface[]|MessageHolderInterface[]
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

            $messages = $this->getMessages($messageHolder);

            if (!$messages) {
                continue;
            }

            foreach ($messages as $message) {
                if ($message instanceof LifeCycleAwareMessageInterface) {
                    $message->preSend($messageHolder);
                }
            }

            $envelopes = array_merge($this->envelopeFactory->createEnvelopes($messageHolder, $messages), $envelopes);
        }

        foreach ($envelopes as $envelope) {
            $this->bus->dispatch($envelope);
        }
    }

    /**
     * @param DeprecatedMessageHolderInterface|MessageHolderInterface $messageHolder
     */
    private function getMessages($messageHolder): array
    {
        $messages = [];

        if ($messageHolder instanceof MessageHolderInterface) {
            $messages = $messageHolder->getOnHoldMessages(true);
        }

        if ($messageHolder instanceof DeprecatedMessageHolderInterface) {
            $queue = $messageHolder->messageQueue();
            while (!$queue->isEmpty()) {
                $messages[] = $queue->dequeue();
            }
        }

        return $messages;
    }

    private function trackMessageHolder(LifecycleEventArgs $event): void
    {
        $entity = $event->getEntity();

        switch (true) {
            case $entity instanceof MessageHolderInterface:
            case $entity instanceof DeprecatedMessageHolderInterface:
                break;
            default:
                return;
        }

        $classMetadata = $this->entityManager->getClassMetadata(get_class($entity));
        $className = $classMetadata->rootEntityName;
        $this->messageHolders[$className][spl_object_id($entity)] = $entity;
    }

    /**
     * @return array|MessageHolderInterface[]|DeprecatedMessageHolderInterface[]
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
