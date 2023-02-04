<?php

namespace Draw\Bundle\UserBundle\Message;

use Draw\Bundle\UserBundle\Entity\PasswordChangeUserInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Message\LifeCycleAwareMessageInterface;

class PasswordChangeRequestedMessage implements LifeCycleAwareMessageInterface
{
    public function __construct(private mixed $userId = null)
    {
    }

    /**
     * @param MessageHolderInterface&PasswordChangeUserInterface $messageHolder
     */
    public function preSend(MessageHolderInterface $messageHolder): void
    {
        $this->userId = $messageHolder->getId();
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
