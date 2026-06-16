<?php

namespace Draw\Component\Security\Tests\Http\EventListener;

use Draw\Component\Security\Http\Authenticator\Passport\Badge\RoleRestrictedBadge;
use Draw\Component\Security\Http\EventListener\RoleRestrictedAuthenticatorListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

/**
 * @internal
 */
#[CoversClass(RoleRestrictedAuthenticatorListener::class)]
class RoleRestrictedAuthenticatorListenerTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $service = new RoleRestrictedAuthenticatorListener(
            static::createStub(RoleHierarchyInterface::class),
        );

        static::assertSame(
            [CheckPassportEvent::class => ['checkPassport', -1]],
            $service::getSubscribedEvents()
        );
    }

    public function testCheckPassportNoRoleRestrictedBadge(): void
    {
        $user = $this->createMock(UserInterface::class);

        $service = new RoleRestrictedAuthenticatorListener(
            $roleHierarchy = $this->createMock(RoleHierarchyInterface::class),
        );

        $roleHierarchy
            ->expects(static::never())
            ->method('getReachableRoleNames')
            ->seal()
        ;

        $user
            ->expects(static::never())
            ->method('getRoles')
            ->seal()
        ;

        $service
            ->checkPassport($this->createCheckPassportEvent($user))
        ;
    }

    public function testCheckPassportRoleDoNotMatch(): void
    {
        $user = $this->createMock(UserInterface::class);

        $service = new RoleRestrictedAuthenticatorListener(
            $roleHierarchy = $this->createMock(RoleHierarchyInterface::class),
        );

        $user
            ->expects(static::once())
            ->method('getRoles')
            ->willReturn($roles = ['ROLE_USER'])
            ->seal()
        ;

        $roleHierarchy
            ->expects(static::once())
            ->method('getReachableRoleNames')
            ->with($roles)
            ->willReturn($roles)
            ->seal()
        ;

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Access denied.');

        $service
            ->checkPassport($this->createCheckPassportEvent($user, [new RoleRestrictedBadge(uniqid('ROLE_'))]))
        ;
    }

    public function testCheckPassportRoleMatch(): void
    {
        $user = $this->createMock(UserInterface::class);

        $service = new RoleRestrictedAuthenticatorListener(
            $roleHierarchy = $this->createMock(RoleHierarchyInterface::class),
        );

        $user
            ->expects(static::once())
            ->method('getRoles')
            ->willReturn($roles = ['ROLE_USER'])
            ->seal()
        ;

        $roleHierarchy
            ->expects(static::once())
            ->method('getReachableRoleNames')
            ->with($roles)
            ->willReturn([...$roles, ...[$role = uniqid('ROLE_')]])
            ->seal()
        ;

        $badge = new RoleRestrictedBadge($role);

        $service
            ->checkPassport($this->createCheckPassportEvent($user, [$badge]))
        ;

        static::assertTrue($badge->isResolved());
    }

    private function createCheckPassportEvent(UserInterface $user, array $badges = []): CheckPassportEvent
    {
        return new CheckPassportEvent(
            static::createStub(AuthenticatorInterface::class),
            new SelfValidatingPassport(
                new UserBadge(
                    uniqid('user-identifier-'),
                    static fn (): UserInterface => $user
                ),
                $badges
            )
        );
    }
}
