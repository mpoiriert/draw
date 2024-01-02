<?php

namespace Draw\Bundle\UserBundle\Message;

use Draw\Bundle\UserBundle\Entity\LockableUserInterface;
use Draw\Component\Core\DateTimeUtils;
use Draw\Component\Messenger\DoctrineMessageBusHook\Message\LifeCycleAwareMessageInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderInterface;

class TemporaryUnlockedMessage implements LifeCycleAwareMessageInterface
{
    private \DateTimeImmutable $until;

    private ?string $userIdentifier = null;

    public function __construct(private bool $wasLocked, \DateTimeInterface $until)
    {
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

        $this->userIdentifier = $messageHolder->getUserIdentifier();
    }
}
