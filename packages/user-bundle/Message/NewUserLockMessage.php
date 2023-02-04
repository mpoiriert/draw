<?php

namespace Draw\Bundle\UserBundle\Message;

class NewUserLockMessage
{
    public function __construct(private string $userLockId)
    {
    }

    public function getUserLockId(): string
    {
        return $this->userLockId;
    }
}
