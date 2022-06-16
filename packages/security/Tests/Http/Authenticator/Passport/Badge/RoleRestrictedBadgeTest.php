<?php

namespace Draw\Component\Security\Tests\Http\Authenticator\Passport\Badge;

use Draw\Component\Security\Http\Authenticator\Passport\Badge\RoleRestrictedBadge;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Security\Http\Authenticator\Passport\Badge\RoleRestrictedBadge
 */
class RoleRestrictedBadgeTest extends TestCase
{
    private RoleRestrictedBadge $entity;

    private string $role;

    public function setUp(): void
    {
        $this->entity = new RoleRestrictedBadge($this->role = uniqid('ROLE_'));
    }

    public function testGetRole(): void
    {
        static::assertSame(
            $this->role,
            $this->entity->getRole()
        );
    }

    public function testIsResolved(): void
    {
        static::assertFalse($this->entity->isResolved());
    }

    public function testMarkResolved(): void
    {
        $this->entity->markResolved();

        static::assertTrue($this->entity->isResolved());
    }
}
