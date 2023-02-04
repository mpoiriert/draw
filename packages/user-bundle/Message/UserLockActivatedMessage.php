<?php

namespace Draw\Bundle\UserBundle\Message;

class UserLockActivatedMessage
{
    public function __construct(private string $userLockId)
    {
    }

    public function getUserLockId(): string
    {
        return $this->userLockId;
    }
}
