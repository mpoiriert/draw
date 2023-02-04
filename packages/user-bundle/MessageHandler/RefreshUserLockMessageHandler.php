<?php

namespace Draw\Bundle\UserBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\AccountLocker;
use Draw\Bundle\UserBundle\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\Message\RefreshUserLockMessage;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class RefreshUserLockMessageHandler implements MessageSubscriberInterface
{
    public static function getHandledMessages(): array
    {
        return [
            RefreshUserLockMessage::class => 'handleRefreshUserLockMessage',
        ];
    }

    /**
     * @param EntityRepository<UserInterface> $drawUserEntityRepository
     */
    public function __construct(
        private AccountLocker $accountLocker,
        private EntityManagerInterface $entityManager,
        private EntityRepository $drawUserEntityRepository
    ) {
    }

    public function handleRefreshUserLockMessage(RefreshUserLockMessage $message): void
    {
        if (!$user = $this->drawUserEntityRepository->find($message->getUserId())) {
            return;
        }

        if (!$user instanceof LockableUserInterface) {
            throw new \UnexpectedValueException(sprintf(
                'Expected instance of [%s], instance of [%s] returned.',
                LockableUserInterface::class,
                $user::class
            ));
        }

        $this->accountLocker->refreshUserLocks($user);

        $this->entityManager->flush();
    }
}
