<?php namespace Draw\Bundle\CommandBundle\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SystemAuthenticator implements SystemAuthenticatorInterface
{
    public function getTokenForSystem(): TokenInterface
    {
        return new SystemToken(['ROLE_SYSTEM']);
    }
}