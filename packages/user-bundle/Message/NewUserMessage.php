<?php

namespace Draw\Bundle\UserBundle\Message;

class NewUserMessage
{
    /**
     * @var mixed
     */
    private $userId;

    /**
     * @param mixed $userId
     */
    public function __construct($userId = null)
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
