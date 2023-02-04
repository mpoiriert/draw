<?php

namespace Draw\Component\Security\Core\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

class SystemToken extends AbstractToken
{
    public function __construct(array $roles = [])
    {
        parent::__construct($roles);
        $this->setUser(new class($roles) implements UserInterface {
            public function __construct(private array $roles)
            {
            }

            public function getRoles(): array
            {
                return $this->roles;
            }

            public function getUserIdentifier(): string
            {
                return 'system';
            }

            public function getPassword(): ?string
            {
                return null;
            }

            public function getSalt(): ?string
            {
                return null;
            }

            public function eraseCredentials(): void
            {
            }

            public function getUsername(): ?string
            {
                return null;
            }
        });
    }

    public function isAuthenticated(): bool
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function getCredentials()
    {
        return null;
    }
}
