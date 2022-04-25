<?php

namespace Draw\Component\Security\Core\Authentication;

use Draw\Component\Security\Core\Authentication\Token\SystemToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SystemAuthenticator implements SystemAuthenticatorInterface
{
    private array $roles;

    public function __construct(array $roles = ['ROLE_SYSTEM'])
    {
        $this->roles = $roles;
    }

    public function getTokenForSystem(): TokenInterface
    {
        return new SystemToken($this->roles);
    }
}
