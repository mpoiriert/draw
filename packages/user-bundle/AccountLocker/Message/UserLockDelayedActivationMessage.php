<?php

namespace Draw\Bundle\UserBundle\AccountLocker\Message;

class UserLockDelayedActivationMessage
{
    private $userLockId;

    public function __construct(string $userLockId)
    {
        $this->userLockId = $userLockId;
    }

    public function getUserLockId(): string
    {
        return $this->userLockId;
    }
}
