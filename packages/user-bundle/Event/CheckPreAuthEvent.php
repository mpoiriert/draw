<?php

namespace Draw\Bundle\UserBundle\Event;

use Symfony\Component\Security\Core\User\UserInterface;

class CheckPreAuthEvent
{
    private $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
