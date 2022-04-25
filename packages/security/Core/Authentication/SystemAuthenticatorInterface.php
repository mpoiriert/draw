<?php

namespace Draw\Component\Security\Core\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface SystemAuthenticatorInterface
{
    public function getTokenForSystem(): TokenInterface;
}
