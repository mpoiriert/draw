<?php

namespace Draw\Bundle\UserBundle\AccountLocker\Event;

use Draw\Bundle\UserBundle\AccountLocker\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\AccountLocker\Entity\UserLock;
use Symfony\Contracts\EventDispatcher\Event;

class GetUserLocksEvent extends Event
{
    private array $locks = [];

    private LockableUserInterface $user;

    public function __construct(LockableUserInterface $user)
    {
        $this->user = $user;
    }

    public function addLock(UserLock $userLock)
    {
        $this->locks[$userLock->getReason()] = $userLock;
    }

    /**
     * @return array<string, UserLock>
     */
    public function getLocks(): array
    {
        return $this->locks;
    }

    public function getUser(): LockableUserInterface
    {
        return $this->user;
    }
}
