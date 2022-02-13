<?php

namespace Draw\Bundle\UserBundle\PasswordChangeEnforcer\Message;

use Draw\Bundle\DoctrineBusMessageBundle\Message\LifeCycleAwareMessageInterface;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Entity\PasswordChangeUserInterface;

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
