<?php

namespace Draw\Component\Messenger\DoctrineMessageBusHook\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Proxy;
use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\EnvelopeFactory\BasicEnvelopeFactory;
use Draw\Component\Messenger\DoctrineMessageBusHook\EnvelopeFactory\EnvelopeFactoryInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Message\LifeCycleAwareMessageInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class DoctrineBusMessageListener implements EventSubscriber
{
    /**
     * @var array|MessageHolderInterface[]
     */
    private array $messageHolders = [];

    private MessageBusInterface $messageBus;

    private EnvelopeFactoryInterface $envelopeFactory;

    public function __construct(
        MessageBusInterface $messageBus,
        ?EnvelopeFactoryInterface $envelopeFactory = null
    ) {
        $this->messageBus = $messageBus;
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

            if (!$messages = $messageHolder->getOnHoldMessages(true)) {
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
            $this->messageBus->dispatch($envelope);
        }
    }

    private function trackMessageHolder(LifecycleEventArgs $event): void
    {
        $entity = $event->getEntity();

        if (!$entity instanceof MessageHolderInterface) {
            return;
        }

        $entityManager = $event->getEntityManager();

        $classMetadata = $entityManager->getClassMetadata(get_class($entity));
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

        return call_user_func_array('array_merge', array_values($this->messageHolders));
    }
}
