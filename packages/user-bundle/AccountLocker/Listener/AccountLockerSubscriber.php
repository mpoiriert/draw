<?php

namespace Draw\Bundle\UserBundle\AccountLocker\Listener;

use Draw\Bundle\UserBundle\AccountLocker\AccountLocker;
use Draw\Bundle\UserBundle\AccountLocker\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\AccountLocker\Entity\UserLock;
use Draw\Bundle\UserBundle\AccountLocker\Event\GetUserLocksEvent;
use Draw\Bundle\UserBundle\AccountLocker\Exception\AccountLockedException;
use Draw\Bundle\UserBundle\Event\CheckPreAuthEvent;
use Draw\Bundle\UserBundle\Event\UserRequestInterceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccountLockerSubscriber implements EventSubscriberInterface
{
    private const INTERCEPTION_REASON = 'account_locked';

    private $accountLocker;

    private $urlGenerator;

    private $accountLockedRoute;

    public static function getSubscribedEvents()
    {
        return [
            UserRequestInterceptionEvent::class => ['handleUserRequestInterceptionEvent', 10000],
            GetUserLocksEvent::class => ['handlerGetUserLocksEvent'],
            CheckPreAuthEvent::class => ['handlerCheckPreAuthEvent'],
        ];
    }

    public function __construct(
        AccountLocker $accountLocker,
        UrlGeneratorInterface $urlGenerator,
        string $accountLockedRoute = 'draw_user_account_locker_account_locked'
    ) {
        $this->accountLocker = $accountLocker;
        $this->urlGenerator = $urlGenerator;
        $this->accountLockedRoute = $accountLockedRoute;
    }

    public function handleUserRequestInterceptionEvent(UserRequestInterceptionEvent $event): void
    {
        $user = $event->getUser();
        switch (true) {
            case !$user instanceof LockableUserInterface:
            case !$this->accountLocker->isLocked($user):
                return;
            case $event->getRequest()->attributes->get('_route') === $this->accountLockedRoute:
                $event->allowHandlingRequest();

                return;
            default:
                $event->setResponse(
                    new RedirectResponse(
                        $this->urlGenerator->generate($this->accountLockedRoute)
                    ),
                    self::INTERCEPTION_REASON
                );
        }
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
        switch (true) {
            case !$user instanceof LockableUserInterface:
            case !($reasons = array_keys($this->accountLocker->getActiveLocks($user))):
                return;
        }

        throw new AccountLockedException('draw_user.account_locker.account_locked_exception', $reasons);
    }
}
