<?php

namespace Draw\Bundle\UserBundle\Message;

class RefreshUserLockMessage
{
    public function __construct(private mixed $userId)
    {
    }

    public function getUserId()
    {
        return $this->userId;
    }
}
