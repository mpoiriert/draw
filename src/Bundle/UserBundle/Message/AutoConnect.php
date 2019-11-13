<?php namespace Draw\Bundle\UserBundle\Message;

class AutoConnect implements AutoConnectInterface
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