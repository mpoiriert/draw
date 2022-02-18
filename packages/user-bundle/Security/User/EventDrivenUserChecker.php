<?php

namespace Draw\Bundle\UserBundle\Security\User;

use Draw\Bundle\UserBundle\Event\CheckPostAuthEvent;
use Draw\Bundle\UserBundle\Event\CheckPreAuthEvent;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventDrivenUserChecker implements UserCheckerInterface
{
    private $decoratedUserChecker;

    private $eventDispatcher;

    public function __construct(UserCheckerInterface $decoratedUserChecker, EventDispatcherInterface $eventDispatcher)
    {
        $this->decoratedUserChecker = $decoratedUserChecker;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function checkPreAuth(UserInterface $user)
    {
        $this->decoratedUserChecker->checkPreAuth($user);

        $this->eventDispatcher->dispatch(new CheckPreAuthEvent($user));
    }

    public function checkPostAuth(UserInterface $user)
    {
        $this->decoratedUserChecker->checkPostAuth($user);

        $this->eventDispatcher->dispatch(new CheckPostAuthEvent($user));
    }
}
