<?php

namespace Draw\Bundle\UserBundle\Sonata\Security;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class AdminLoginBadge implements BadgeInterface
{
    private bool $resolved = false;

    private string $role;

    public function __construct(string $role)
    {
        $this->role = $role;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function markResolved(): void
    {
        $this->resolved = true;
    }

    public function isResolved(): bool
    {
        return $this->resolved;
    }
}
