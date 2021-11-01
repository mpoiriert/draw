<?php

namespace App\Message;

use App\Entity\User;

class UserCreatedMessage
{
    public $userId;

    public function __construct(User $user)
    {
        $this->userId = $user->getId();
    }
}
