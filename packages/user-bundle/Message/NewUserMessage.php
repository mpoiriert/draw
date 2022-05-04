<?php

namespace Draw\Bundle\UserBundle\Message;

class NewUserMessage
{
    private $userId;

    public function __construct($userId = null)
    {
        $this->userId = $userId;
    }

    public function getUserId()
    {
        return $this->userId;
    }
}
