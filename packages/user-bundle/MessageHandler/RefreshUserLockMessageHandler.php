<?php

namespace Draw\Bundle\UserBundle\MessageHandler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\AccountLocker;
use Draw\Bundle\UserBundle\Message\RefreshUserLockMessage;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class RefreshUserLockMessageHandler implements MessageSubscriberInterface
{
    private AccountLocker $accountLocker;

    private EntityManager $entityManager;

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

        $this->accountLocker->refreshUserLocks($user);

        $this->entityManager->flush();
    }
}
