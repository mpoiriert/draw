<?php

namespace Draw\Component\Security\Core\Event;

use Symfony\Component\Security\Core\User\UserInterface;

class CheckPreAuthEvent
{
    public function __construct(private UserInterface $user)
    {
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
