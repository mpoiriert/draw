<?php

namespace Draw\Component\Security\Core\User;

use Draw\Component\Security\Core\Event\CheckPostAuthEvent;
use Draw\Component\Security\Core\Event\CheckPreAuthEvent;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventDrivenUserChecker implements UserCheckerInterface
{
    public function __construct(
        private UserCheckerInterface $decoratedUserChecker,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        $this->decoratedUserChecker->checkPreAuth($user);

        $this->eventDispatcher->dispatch(new CheckPreAuthEvent($user));
    }

    public function checkPostAuth(UserInterface $user): void
    {
        $this->decoratedUserChecker->checkPostAuth($user);

        $this->eventDispatcher->dispatch(new CheckPostAuthEvent($user));
    }
}
