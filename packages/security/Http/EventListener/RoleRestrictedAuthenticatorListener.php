<?php

namespace Draw\Component\Security\Http\EventListener;

use Draw\Component\Security\Http\Authenticator\Passport\Badge\RoleRestrictedBadge;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class RoleRestrictedAuthenticatorListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [CheckPassportEvent::class => ['checkPassport', -1]];
    }

    public function __construct(private RoleHierarchyInterface $roleHierarchy)
    {
    }

    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();

        if (!$badge = $passport->getBadge(RoleRestrictedBadge::class)) {
            return;
        }

        $user = $passport->getBadge(UserBadge::class)->getUser();

        if (!\in_array($badge->getRole(), $this->roleHierarchy->getReachableRoleNames($user->getRoles()), true)) {
            throw new CustomUserMessageAuthenticationException('Access denied.');
        }

        $badge->markResolved();
    }
}
