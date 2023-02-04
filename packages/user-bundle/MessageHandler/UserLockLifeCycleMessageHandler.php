<?php

namespace Draw\Bundle\UserBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Draw\Bundle\UserBundle\Message\NewUserLockMessage;
use Draw\Bundle\UserBundle\Message\UserLockActivatedMessage;
use Draw\Bundle\UserBundle\Message\UserLockDelayedActivationMessage;
use Draw\Component\Core\DateTimeUtils;
use Draw\Component\Messenger\Searchable\Stamp\SearchableTagStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

class UserLockLifeCycleMessageHandler implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield NewUserLockMessage::class => 'handleNewUserLockMessage';
        yield UserLockDelayedActivationMessage::class => 'handleUserLockDelayedActivationMessage';
    }

    public function __construct(private MessageBusInterface $messageBus, private EntityManagerInterface $entityManager)
    {
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
            case $lockOn <= new \DateTimeImmutable():
                $this->messageBus->dispatch(new Envelope(
                    new UserLockActivatedMessage($userLockId),
                    [
                        new DispatchAfterCurrentBusStamp(),
                    ]
                ));
                break;
            default:
                $stamps = [
                    new DelayStamp(DateTimeUtils::millisecondDiff($lockOn)),
                    new DispatchAfterCurrentBusStamp(),
                ];

                if (class_exists(SearchableTagStamp::class)) {
                    $stamps[] = new SearchableTagStamp(
                        [
                            'activateUserLock:'.$userLock->getReason(),
                            'userId:'.$userLock->getUser()->getUserIdentifier(),
                        ],
                        true
                    );
                }
                $this->messageBus->dispatch(new Envelope(new UserLockDelayedActivationMessage($userLockId), $stamps));
                break;
        }
    }
}
