<?php

namespace Draw\Bundle\UserBundle\Message;

class NewUserMessage
{
    public function __construct(private string|int|null $userId = null)
    {
    }

    public function getUserId(): string|int|null
    {
        return $this->userId;
    }
}
