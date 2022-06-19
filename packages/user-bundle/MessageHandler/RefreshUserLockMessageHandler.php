<?php

namespace Draw\Bundle\UserBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\AccountLocker;
use Draw\Bundle\UserBundle\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\Message\RefreshUserLockMessage;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class RefreshUserLockMessageHandler implements MessageSubscriberInterface
{
    private AccountLocker $accountLocker;

    private EntityManagerInterface $entityManager;

    private EntityRepository $userEntityRepository;

    public static function getHandledMessages(): array
    {
        return [
            RefreshUserLockMessage::class => 'handleRefreshUserLockMessage',
        ];
    }

    public function __construct(
        AccountLocker $accountLocker,
        EntityManagerInterface $entityManager,
        EntityRepository $drawUserEntityRepository
    ) {
        $this->accountLocker = $accountLocker;
        $this->entityManager = $entityManager;
        $this->userEntityRepository = $drawUserEntityRepository;
    }

    public function handleRefreshUserLockMessage(RefreshUserLockMessage $message): void
    {
        if (!$user = $this->userEntityRepository->find($message->getUserId())) {
            return;
        }

        if (!$user instanceof LockableUserInterface) {
            throw new \UnexpectedValueException(sprintf(
                'Expected instance of [%s], instance of [%s] returned.',
                LockableUserInterface::class,
                \get_class($user)
            ));
        }

        $this->accountLocker->refreshUserLocks($user);

        $this->entityManager->flush();
    }
}
