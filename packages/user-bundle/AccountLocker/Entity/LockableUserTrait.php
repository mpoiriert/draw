<?php

namespace Draw\Bundle\UserBundle\AccountLocker\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait LockableUserTrait
{
    /**
     * @ORM\Column(name="has_manual_lock", type="boolean", nullable=false, options={"default":"0"})
     */
    private $hasManualLock = false;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Draw\Bundle\UserBundle\AccountLocker\Entity\UserLock",
     *      orphanRemoval=true,
     *      mappedBy="user",
     *      cascade={"persist"}
     * )
     * @ORM\OrderBy({"lockOn":"ASC", "createdAt":"ASC"})
     */
    private $userLocks = null;

    public function getHasManualLock(): bool
    {
        return $this->hasManualLock;
    }

    public function setHasManualLock(bool $hasManualLock): void
    {
        if ($this->hasManualLock === $hasManualLock) {
            return;
        }

        $this->hasManualLock = $hasManualLock;

        if ($this->hasManualLock) {
            $this->lock(new UserLock(UserLock::REASON_MANUAL_LOCK));
        } else {
            $this->unlock(UserLock::REASON_MANUAL_LOCK);
        }
    }

    public function lock(UserLock $userLock): UserLock
    {
        if (!$reason = $userLock->getReason()) {
            throw new \RuntimeException('User Lock must have a reason at this point.');
        }

        $lock = ($this->getLocks()[$reason] ?? $userLock)
            ->merge($userLock);

        $this->addUserLock($lock);

        return $lock;
    }

    public function unlock(string $reason, DateTimeInterface $until = null): ?UserLock
    {
        switch (true) {
            case null === $lock = $this->getLocks()[$reason] ?? null:
                break;
            case null === $until:
                $this->getUserLocks()->removeElement($lock);
                break;
            default:
                $lock->setUnlockUntil($until);
                break;
        }

        return $lock;
    }

    /**
     * @return array|UserLock[]
     */
    public function getLocks(): array
    {
        $locks = [];
        /** @var UserLock $lock */
        foreach ($this->getUserLocks()->toArray() as $lock) {
            $locks[$lock->getReason()] = $lock;
        }

        return $locks;
    }

    public function getUserLocks(): Collection
    {
        return $this->userLocks ?? $this->userLocks = new ArrayCollection();
    }

    private function addUserLock(UserLock $userLock): void
    {
        $userLocks = $this->getUserLocks();
        if (!$userLocks->contains($userLock)) {
            $userLocks->add($userLock);
            $userLock->setUser($this);
        }
    }
}
