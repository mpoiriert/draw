<?php

namespace Draw\Bundle\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\UserBundle\Message\NewUserLockMessage;
use Draw\Bundle\UserBundle\Message\TemporaryUnlockedMessage;

use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderTrait;

use function Draw\Component\Core\use_trait;

trait LockableUserTrait
{
    /**
     * @ORM\Column(name="manual_lock", type="boolean", nullable=false, options={"default":"0"})
     */
    private bool $manualLock = false;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Draw\Bundle\UserBundle\Entity\UserLock",
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

    public function setManualLock(bool $manualLock): self
    {
        if ($this->manualLock === $manualLock) {
            return $this;
        }

        $this->manualLock = $manualLock;

        if ($this->manualLock) {
            $this->lock(new UserLock(UserLock::REASON_MANUAL_LOCK));
        } else {
            $this->unlock(UserLock::REASON_MANUAL_LOCK);
        }

        return $this;
    }

    public function lock(UserLock $userLock): UserLock
    {
        if (!$reason = $userLock->getReason()) {
            throw new \RuntimeException('User Lock must have a reason at this point.');
        }

        $currentLock = $this->getLocks()[$reason] ?? null;

        if ($currentLock && $currentLock->isSame($userLock)) {
            return $currentLock;
        }

        if ($currentLock) {
            $userLock->setUnlockUntil($currentLock->getUnlockUntil());
            $this->getUserLocks()->removeElement($currentLock);
        }

        $this->addUserLock($userLock);

        return $userLock;
    }

    public function unlock(string $reason, ?\DateTimeInterface $until = null): ?UserLock
    {
        switch (true) {
            case null === $lock = $this->getLocks()[$reason] ?? null:
                break;
            case null === $until:
                $this->removeUserLock($lock);
                break;
            default:
                $lock->setUnlockUntil($until);
                break;
        }

        return $lock;
    }

    public function temporaryUnlockAll(\DateTimeInterface $until): void
    {
        $wasLocked = $this->isLocked();

        foreach ($this->getLocks() as $lock) {
            $this->unlock($lock->getReason(), $until);
        }

        if (use_trait($this, MessageHolderTrait::class)) {
            $this->onHoldMessages[TemporaryUnlockedMessage::class] = new TemporaryUnlockedMessage(
                $wasLocked,
                $until
            );
        }
    }

    /**
     * @return array<string, UserLock>
     *
     * @phpstan-return  array<string, UserLock>
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

    public function isLocked(): bool
    {
        foreach ($this->getUserLocks() as $userLock) {
            if ($userLock->isActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection|UserLock[]
     */
    public function getUserLocks(): Collection
    {
        return $this->userLocks ?? $this->userLocks = new ArrayCollection();
    }

    /**
     * @internal This is directly use in sonata admin. Use lock method instead.
     */
    public function addUserLock(UserLock $userLock): self
    {
        $userLocks = $this->getUserLocks();

        // This is to prevent sonata admin to add the lock manually if has manual lock was set to false
        if (UserLock::REASON_MANUAL_LOCK === $userLock->getReason() && !$this->hasManualLock()) {
            return $this;
        }

        if (!$userLocks->contains($userLock)) {
            if (use_trait($this, MessageHolderTrait::class)) {
                $this->onHoldMessages['user-lock-'.$userLock->getReason()] = new NewUserLockMessage($userLock->getId());
            }

            $userLocks->add($userLock);
            $userLock->setUser($this);
        }

        return $this;
    }

    public function removeUserLock(UserLock $userLock): self
    {
        if (UserLock::REASON_MANUAL_LOCK === $userLock->getReason() && $this->hasManualLock()) {
            return $this;
        }

        $this->getUserLocks()->removeElement($userLock);

        return $this;
    }
}
