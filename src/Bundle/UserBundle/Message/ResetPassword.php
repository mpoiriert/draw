<?php namespace Draw\Bundle\UserBundle\Message;

class ResetPassword
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

    public function isSingleUseToken(): bool
    {
        return true;
    }
}