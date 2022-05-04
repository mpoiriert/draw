<?php

namespace Draw\Bundle\UserBundle\Message;

class NewUserLockMessage
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
