<?php

namespace Draw\Bundle\UserBundle\Sonata\Security;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class CheckAdminFormLoginListener implements EventSubscriberInterface
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
        $passport = $event->getPassport();
        $badge = $event->getPassport()->getBadge(AdminLoginBadge::class);

        if (!$badge) {
            return;
        }

        $user = $passport->getBadge(UserBadge::class)->getUser();

        if (!in_array($badge->getRole(), $this->roleHierarchy->getReachableRoleNames($user->getRoles()))) {
            throw new CustomUserMessageAuthenticationException("You don't have permission to access that page.");
        }

        $badge->markResolved();
    }
}
