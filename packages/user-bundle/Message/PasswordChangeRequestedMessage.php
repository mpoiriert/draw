<?php

namespace Draw\Bundle\UserBundle\Message;

use Draw\Bundle\UserBundle\Entity\PasswordChangeUserInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Message\LifeCycleAwareMessageInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderInterface;

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
