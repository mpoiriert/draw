<?php

namespace Draw\Component\Security\Http\EventListener;

use Draw\Component\Security\Http\Authenticator\Passport\Badge\RoleRestrictedBadge;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class RoleRestrictedAuthenticatorListener implements EventSubscriberInterface
{
    private RoleHierarchyInterface $roleHierarchy;

    public static function getSubscribedEvents(): array
    {
        return [CheckPassportEvent::class => ['checkPassport', -1]];
    }

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    public function checkPassport(CheckPassportEvent $event): void
    {
        /** @var Passport $passport */
        $passport = $event->getPassport();

        if (!$badge = $passport->getBadge(RoleRestrictedBadge::class)) {
            return;
        }

        $user = $passport->getBadge(UserBadge::class)->getUser();

        if (!\in_array($badge->getRole(), $this->roleHierarchy->getReachableRoleNames($user->getRoles()))) {
            throw new CustomUserMessageAuthenticationException('Access denied.');
        }

        $badge->markResolved();
    }
}
