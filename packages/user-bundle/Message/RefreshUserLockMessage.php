<?php

namespace Draw\Bundle\UserBundle\Message;

class RefreshUserLockMessage
{
    /**
     * @var mixed
     */
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
