<?php

namespace Draw\Bundle\UserBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\AccountLocker;
use Draw\Bundle\UserBundle\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\Message\RefreshUserLockMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\User\UserInterface;

class RefreshUserLockMessageHandler
{
    /**
     * @param EntityRepository<UserInterface> $drawUserEntityRepository
     */
    public function __construct(
        private AccountLocker $accountLocker,
        private EntityManagerInterface $entityManager,
        private EntityRepository $drawUserEntityRepository,
    ) {
    }

    #[AsMessageHandler]
    public function handleRefreshUserLockMessage(RefreshUserLockMessage $message): void
    {
        if (!$user = $this->drawUserEntityRepository->find($message->getUserId())) {
            return;
        }

        if (!$user instanceof LockableUserInterface) {
            throw new \UnexpectedValueException(\sprintf('Expected instance of [%s], instance of [%s] returned.', LockableUserInterface::class, $user::class));
        }

        $this->accountLocker->refreshUserLocks($user);

        $this->entityManager->flush();
    }
}
