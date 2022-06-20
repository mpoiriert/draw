<?php

namespace Draw\Bundle\UserBundle\Message;

class RefreshUserLockMessage
{
    /**
     * @var mixed
     */
    private $userId;

    /**
     * @param mixed $userId
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
