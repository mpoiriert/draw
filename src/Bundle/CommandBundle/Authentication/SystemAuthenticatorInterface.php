<?php namespace Draw\Bundle\CommandBundle\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface SystemAuthenticatorInterface
{
    public function getTokenForSystem(): TokenInterface;
}