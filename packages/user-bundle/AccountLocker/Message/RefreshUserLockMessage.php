<?php

namespace Draw\Bundle\UserBundle\AccountLocker\Message;

class RefreshUserLockMessage
{
    private $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function getUserId()
    {
        return $this->userId;
    }
}
