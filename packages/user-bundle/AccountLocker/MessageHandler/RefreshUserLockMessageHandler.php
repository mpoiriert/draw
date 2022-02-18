<?php

namespace Draw\Bundle\UserBundle\AccountLocker\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\AccountLocker\AccountLocker;
use Draw\Bundle\UserBundle\AccountLocker\Message\RefreshUserLockMessage;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class RefreshUserLockMessageHandler implements MessageSubscriberInterface
{
    private $accountLocker;

    private $entityManager;

    private $userEntityRepository;

    public static function getHandledMessages(): iterable
    {
        yield RefreshUserLockMessage::class => 'handleRefreshUserLockMessage';
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
