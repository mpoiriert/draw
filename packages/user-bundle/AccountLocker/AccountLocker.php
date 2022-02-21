<?php

namespace Draw\Bundle\UserBundle\AccountLocker;

use Draw\Bundle\UserBundle\AccountLocker\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\AccountLocker\Entity\UserLock;
use Draw\Bundle\UserBundle\AccountLocker\Event\GetUserLocksEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AccountLocker
{
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function isLocked(LockableUserInterface $user): bool
    {
        return 0 !== count($this->getActiveLocks($user));
    }

    /**
     * @return array|UserLock[]
     */
    public function refreshUserLocks(LockableUserInterface $user): array
    {
        $this->eventDispatcher->dispatch($event = new GetUserLocksEvent($user));

        $currentLocks = $user->getLocks();
        $locks = $event->getLocks();
        foreach ($locks as $userLock) {
            $user->lock($userLock);
            unset($currentLocks[$userLock->getReason()]);
        }

        foreach (array_keys($currentLocks) as $reason) {
            $user->unlock($reason);
        }

        return $user->getLocks();
    }

    /**
     * @return array|UserLock[]
     */
    public function getActiveLocks(LockableUserInterface $user): array
    {
        return array_filter(
            $this->refreshUserLocks($user),
            function (UserLock $userLock) {
                return $userLock->isActive();
            }
        );
    }
}
