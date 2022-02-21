<?php

namespace Draw\Bundle\UserBundle\AccountLocker\MessageHandler;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\UserBundle\AccountLocker\Entity\UserLock;
use Draw\Bundle\UserBundle\AccountLocker\Message\NewUserLockMessage;
use Draw\Bundle\UserBundle\AccountLocker\Message\UserLockActivatedMessage;
use Draw\Bundle\UserBundle\AccountLocker\Message\UserLockDelayedActivationMessage;
use Draw\Component\Core\DateTimeUtils;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

class UserLockLifeCycleMessageHandler implements MessageSubscriberInterface
{
    private $entityManager;

    private $messageBus;

    public static function getHandledMessages(): iterable
    {
        yield NewUserLockMessage::class => 'handleNewUserLockMessage';
        yield UserLockDelayedActivationMessage::class => 'handleUserLockDelayedActivationMessage';
    }

    public function __construct(MessageBusInterface $messageBus, EntityManagerInterface $entityManager)
    {
        $this->messageBus = $messageBus;
        $this->entityManager = $entityManager;
    }

    public function handleNewUserLockMessage(NewUserLockMessage $message): void
    {
        $this->sendUserLockLifeCycleMessage($message->getUserLockId());
    }

    public function handleUserLockDelayedActivationMessage(UserLockDelayedActivationMessage $message): void
    {
        $this->sendUserLockLifeCycleMessage($message->getUserLockId());
    }

    private function sendUserLockLifeCycleMessage(string $userLockId): void
    {
        $userLock = $this->entityManager->find(UserLock::class, $userLockId);

        switch (true) {
            case null === $userLock:
                return;
            case null === $lockOn = $userLock->getLockOn():
            case $lockOn <= new DateTimeImmutable():
                $this->messageBus->dispatch(new Envelope(
                    new UserLockActivatedMessage($userLockId),
                    [
                        new DispatchAfterCurrentBusStamp(),
                    ]
                ));
                break;
            default:
                $this->messageBus->dispatch(new Envelope(
                    new UserLockDelayedActivationMessage($userLockId),
                    [
                        new DelayStamp(DateTimeUtils::millisecondDiff($lockOn)),
                        new DispatchAfterCurrentBusStamp(),
                    ]
                ));
                break;
        }
    }
}
