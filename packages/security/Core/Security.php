<?php

namespace Draw\Component\Security\Core;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Security
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function getUser(): ?UserInterface
    {
        return $this->getToken()?->getUser();
    }

    public function isGranted(mixed $attributes, mixed $subject = null): bool
    {
        return $this->authorizationChecker->isGranted($attributes, $subject);
    }

    public function getToken(): ?TokenInterface
    {
        return $this->tokenStorage->getToken();
    }
}
