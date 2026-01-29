<?php

namespace Draw\Component\Messenger\DoctrineMessageBusHook\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\OnClearEventArgs;
use Doctrine\Persistence\Proxy;
use Draw\Component\Messenger\DoctrineMessageBusHook\EnvelopeFactory\EnvelopeFactoryInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Message\LifeCycleAwareMessageInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\ResetInterface;

class DoctrineBusMessageListener implements ResetInterface
{
    /**
     * @var MessageHolderInterface[]
     */
    private array $messageHolders = [];

    public function __construct(
        private MessageBusInterface $messageBus,
        private EnvelopeFactoryInterface $envelopeFactory,
    ) {
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
        $this->messageHolders = [];
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
        $entity = $event->getObject();

        if (!$entity instanceof MessageHolderInterface) {
            return;
        }

        $entityManager = $event->getObjectManager();

        $classMetadata = $entityManager->getClassMetadata($entity::class);

        $className = $classMetadata->rootDocumentName
            ?? $classMetadata->rootEntityName
            ?? $classMetadata->getName();

        $this->messageHolders[$className][spl_object_id($entity)] = $entity;
    }

    /**
     * @return array<MessageHolderInterface>
     *
     * @internal
     */
    public function getFlattenMessageHolders(): array
    {
        if (!$this->messageHolders) {
            return [];
        }

        return \call_user_func_array('array_merge', array_values($this->messageHolders));
    }

    public function reset(): void
    {
        $this->messageHolders = [];
    }
}
