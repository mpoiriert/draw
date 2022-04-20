<?php

namespace Draw\Bundle\UserBundle\AccountLocker\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\UserBundle\AccountLocker\Message\NewUserLockMessage;
use function Draw\Component\Core\use_trait;
use Draw\Component\Messenger\Entity\MessageHolderTrait;
use RuntimeException;

trait LockableUserTrait
{
    /**
     * @ORM\Column(name="manual_lock", type="boolean", nullable=false, options={"default":"0"})
     */
    private bool $manualLock = false;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Draw\Bundle\UserBundle\AccountLocker\Entity\UserLock",
     *      orphanRemoval=true,
     *      mappedBy="user",
     *      cascade={"persist"}
     * )
     * @ORM\OrderBy({"lockOn":"ASC", "createdAt":"ASC"})
     */
    private ?Collection $userLocks = null;

    public function hasManualLock(): bool
    {
        return $this->manualLock;
    }

    public function setManualLock(bool $manualLock): void
    {
        if ($this->manualLock === $manualLock) {
            return;
        }

        $this->manualLock = $manualLock;

        if ($this->manualLock) {
            $this->lock(new UserLock(UserLock::REASON_MANUAL_LOCK));
        } else {
            $this->unlock(UserLock::REASON_MANUAL_LOCK);
        }
    }

    public function lock(UserLock $userLock): UserLock
    {
        if (!$reason = $userLock->getReason()) {
            throw new RuntimeException('User Lock must have a reason at this point.');
        }

        $currentLock = $this->getLocks()[$reason] ?? null;
        switch (true) {
            case $userLock === $currentLock:
            case null !== $currentLock && $currentLock->isSame($userLock):
                return $currentLock;
        }

        if ($currentLock) {
            $userLock->setUnlockUntil($currentLock->getUnlockUntil());
            $this->getUserLocks()->removeElement($currentLock);
        }
        $this->addUserLock($userLock);

        return $userLock;
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
            if (use_trait($this, MessageHolderTrait::class)) {
                $this->onHoldMessages['user-lock-'.$userLock->getReason()] = new NewUserLockMessage($userLock->getId());
            }

            $userLocks->add($userLock);
            $userLock->setUser($this);
        }
    }
}
