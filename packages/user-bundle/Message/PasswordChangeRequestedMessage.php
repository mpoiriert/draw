<?php

namespace Draw\Bundle\UserBundle\Message;

use Draw\Bundle\UserBundle\Entity\PasswordChangeUserInterface;
use Draw\Component\Messenger\Message\LifeCycleAwareMessageInterface;

class PasswordChangeRequestedMessage implements LifeCycleAwareMessageInterface
{
    private $userId;

    public function __construct($userId = null)
    {
        $this->userId = $userId;
    }

    /**
     * @param PasswordChangeUserInterface $messageHolder
     */
    public function preSend($messageHolder): void
    {
        $this->userId = $messageHolder->getId();
    }

    public function getUserId()
    {
        return $this->userId;
    }
}
