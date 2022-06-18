<?php

namespace Draw\Bundle\UserBundle\Message;

use Draw\Bundle\UserBundle\Entity\LockableUserInterface;
use Draw\Component\Core\DateTimeUtils;
use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Message\LifeCycleAwareMessageInterface;

class TemporaryUnlockedMessage implements LifeCycleAwareMessageInterface
{
    private bool $wasLocked;

    private \DateTimeImmutable $until;

    private ?string $userIdentifier = null;

    public function __construct(bool $wasLocked, \DateTimeInterface $until)
    {
        $this->wasLocked = $wasLocked;
        $this->until = DateTimeUtils::toDateTimeImmutable($until);
    }

    public function wasLocked(): bool
    {
        return $this->wasLocked;
    }

    public function until(): ?\DateTimeImmutable
    {
        return $this->until;
    }

    public function getUserIdentifier(): ?string
    {
        return $this->userIdentifier;
    }

    public function preSend(MessageHolderInterface $messageHolder): void
    {
        if (!$messageHolder instanceof LockableUserInterface) {
            throw new \LogicException(sprintf('The parameter [%s] must implement interfaced [%s]', '$messageHolder', LockableUserInterface::class));
        }

        $this->setUser($messageHolder);
    }

    private function setUser(LockableUserInterface $user): void
    {
        $this->userIdentifier = $user->getUserIdentifier();
    }
}
