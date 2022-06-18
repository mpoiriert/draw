<?php

namespace Draw\Bundle\UserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface LockableUserInterface extends UserInterface
{
    public function hasManualLock(): bool;

    public function lock(UserLock $userLock): UserLock;

    public function unlock(string $reason, ?\DateTimeInterface $until = null): ?UserLock;

    public function temporaryUnlockAll(\DateTimeInterface $until): void;

    /**
     * @return array<string,UserLock>|UserLock[]
     */
    public function getLocks(): array;

    public function isLocked(): bool;
}
