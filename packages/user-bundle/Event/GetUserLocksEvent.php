<?php

namespace Draw\Bundle\UserBundle\Event;

use Draw\Bundle\UserBundle\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Symfony\Contracts\EventDispatcher\Event;

class GetUserLocksEvent extends Event
{
    /**
     * @var array<string, UserLock>
     */
    private array $locks = [];

    public function __construct(private LockableUserInterface $user)
    {
    }

    public function addLock(UserLock $userLock): void
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
