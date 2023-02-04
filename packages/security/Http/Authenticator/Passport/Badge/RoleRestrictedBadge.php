<?php

namespace Draw\Component\Security\Http\Authenticator\Passport\Badge;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class RoleRestrictedBadge implements BadgeInterface
{
    private bool $resolved = false;

    public function __construct(private string $role)
    {
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
