<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Draw\Bundle\DoctrineBusMessageBundle\MessageHolderInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class DoctrineBusMessageEventSubscriber implements EventSubscriber
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postRemove,
            Events::postUpdate,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->sendBusMessages($args->getObject());
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $this->sendBusMessages($args->getObject());
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->sendBusMessages($args->getObject());
    }

    private function sendBusMessages($object)
    {
        if (!$object instanceof MessageHolderInterface) {
            return;
        }

        $queue = $object->messageQueue();

        while (!$queue->isEmpty()) {
            $message = $queue->dequeue();
            $this->bus->dispatch($message);
        }
    }
}