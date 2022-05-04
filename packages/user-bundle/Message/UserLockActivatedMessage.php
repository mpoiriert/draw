<?php

namespace Draw\Bundle\UserBundle\Message;

class UserLockActivatedMessage
{
    private string $userLockId;

    public function __construct(string $userLockId)
    {
        $this->userLockId = $userLockId;
    }

    public function getUserLockId(): string
    {
        return $this->userLockId;
    }
}
