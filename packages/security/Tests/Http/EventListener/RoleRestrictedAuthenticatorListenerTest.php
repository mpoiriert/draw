<?php

namespace Draw\Component\Security\Tests\Http\EventListener;

use Draw\Component\Security\Http\Authenticator\Passport\Badge\RoleRestrictedBadge;
use Draw\Component\Security\Http\EventListener\RoleRestrictedAuthenticatorListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

/**
 * @covers \Draw\Component\Security\Http\EventListener\RoleRestrictedAuthenticatorListener
 */
class RoleRestrictedAuthenticatorListenerTest extends TestCase
{
    private RoleRestrictedAuthenticatorListener $service;

    private RoleHierarchyInterface $roleHierarchy;

    private UserInterface $user;

    public function setUp(): void
    {
        $this->user = $this->createMock(UserInterface::class);

        $this->service = new RoleRestrictedAuthenticatorListener(
            $this->roleHierarchy = $this->createMock(RoleHierarchyInterface::class),
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->service
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [CheckPassportEvent::class => ['checkPassport', -1]],
            $this->service::getSubscribedEvents()
        );
    }

    public function testCheckPassportNoRoleRestrictedBadge(): void
    {
        $this->roleHierarchy
            ->expects($this->never())
            ->method('getReachableRoleNames');

        $this->user
            ->expects($this->never())
            ->method('getRoles');

        $this->service
            ->checkPassport($this->createCheckPassportEvent());
    }

    public function testCheckPassportRoleDoNotMatch(): void
    {
        $this->user
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn($roles = ['ROLE_USER']);

        $this->roleHierarchy
            ->expects($this->once())
            ->method('getReachableRoleNames')
            ->with($roles)
            ->willReturn($roles);

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Access denied.');

        $this->service
            ->checkPassport($this->createCheckPassportEvent([new RoleRestrictedBadge(uniqid('ROLE_'))]));
    }

    public function testCheckPassportRoleMatch(): void
    {
        $this->user
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn($roles = ['ROLE_USER']);

        $this->roleHierarchy
            ->expects($this->once())
            ->method('getReachableRoleNames')
            ->with($roles)
            ->willReturn(array_merge($roles, [$role = uniqid('ROLE_')]));

        $badge = new RoleRestrictedBadge($role);

        $this->service
            ->checkPassport($this->createCheckPassportEvent([$badge]));

        $this->assertTrue($badge->isResolved());
    }

    private function createCheckPassportEvent(array $badges = []): CheckPassportEvent
    {
        return new CheckPassportEvent(
            $this->createMock(AuthenticatorInterface::class),
            new SelfValidatingPassport(
                new UserBadge(
                    uniqid('user-identifier-'),
                    function () {
                        return $this->user;
                    }
                ),
                $badges
            )
        );
    }
}
