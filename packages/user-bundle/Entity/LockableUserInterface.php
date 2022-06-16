<?php

namespace Draw\Bundle\UserBundle\Entity;

use DateTimeInterface;

interface LockableUserInterface
{
    public function hasManualLock(): bool;

    public function lock(UserLock $userLock): UserLock;

    public function unlock(string $reason, DateTimeInterface $until = null): ?UserLock;

    /**
     * @return array<string,UserLock>|UserLock[]
     */
    public function getLocks(): array;
}
