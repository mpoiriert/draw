<?php

namespace Draw\Component\Security\Core\Event;

use Symfony\Component\Security\Core\User\UserInterface;

class CheckPostAuthEvent
{
    private UserInterface $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
