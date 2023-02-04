<?php

namespace Draw\Bundle\UserBundle\Message;

class RefreshUserLockMessage
{
    public function __construct(private mixed $userId)
    {
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
