<?php

namespace Draw\Bundle\UserBundle\EventListener;

use Draw\Bundle\UserBundle\AccountLocker;
use Draw\Bundle\UserBundle\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Draw\Bundle\UserBundle\Event\GetUserLocksEvent;
use Draw\Bundle\UserBundle\Event\UserRequestInterceptionEvent;
use Draw\Bundle\UserBundle\Exception\AccountLockedException;
use Draw\Bundle\UserBundle\Feed\UserFeedInterface;
use Draw\Component\Security\Core\Event\CheckPreAuthEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccountLockerListener implements EventSubscriberInterface
{
    private const INTERCEPTION_REASON = 'account_locked';

    public static function getSubscribedEvents(): array
    {
        return [
            UserRequestInterceptionEvent::class => ['handleUserRequestInterceptionEvent', 10000],
            GetUserLocksEvent::class => ['handlerGetUserLocksEvent'],
            CheckPreAuthEvent::class => ['handlerCheckPreAuthEvent'],
        ];
    }

    public function __construct(
        private AccountLocker $accountLocker,
        private UrlGeneratorInterface $urlGenerator,
        private UserFeedInterface $userFeed,
        private string $accountLockedRoute = 'draw_user_account_locker_account_locked'
    ) {
    }

    public function handleUserRequestInterceptionEvent(UserRequestInterceptionEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof LockableUserInterface) {
            return;
        }

        if (!$this->accountLocker->isLocked($user)) {
            return;
        }

        if ($event->getRequest()->attributes->get('_route') === $this->accountLockedRoute) {
            $event->allowHandlingRequest();

            return;
        }

        $this->userFeed->addToFeed($user, 'error', 'account_locked');
        $event->setResponse(
            new RedirectResponse(
                $this->urlGenerator->generate($this->accountLockedRoute)
            ),
            self::INTERCEPTION_REASON
        );
    }

    public function handlerGetUserLocksEvent(GetUserLocksEvent $event): void
    {
        if ($event->getUser()->hasManualLock()) {
            $event->addLock(new UserLock(UserLock::REASON_MANUAL_LOCK));
        }
    }

    public function handlerCheckPreAuthEvent(CheckPreAuthEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof LockableUserInterface) {
            return;
        }

        if (empty($reasons = array_keys($this->accountLocker->getActiveLocks($user)))) {
            return;
        }

        $this->userFeed->addToFeed($user, 'error', 'account_locked');

        throw new AccountLockedException('draw_user.account_locker.account_locked_exception', $reasons);
    }
}
