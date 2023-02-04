<?php

namespace Draw\Component\Security\Core\Authentication;

use Draw\Component\Security\Core\Authentication\Token\SystemToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SystemAuthenticator implements SystemAuthenticatorInterface
{
    public function __construct(private array $roles = ['ROLE_SYSTEM'])
    {
    }

    public function getTokenForSystem(): TokenInterface
    {
        return new SystemToken($this->roles);
    }
}
