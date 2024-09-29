<?php

namespace App\DTO;

use App\Entity\User;

class SimpleUser
{
    public string $id;

    public string $email;

    public bool $needChangePassword;

    public function __construct(
        User $user,
    ) {
        $this->id = $user->getId();
        $this->needChangePassword = $user->getNeedChangePassword();
        $this->email = $user->getEmail();
    }
}
