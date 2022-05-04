<?php

namespace Draw\Bundle\UserBundle\MessageHandler;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\Email\PasswordChangeRequestedEmail;
use Draw\Bundle\UserBundle\Entity\PasswordChangeUserInterface;
use Draw\Bundle\UserBundle\Message\PasswordChangeRequestedMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PasswordChangeRequestedSendEmailMessageHandler implements MessageHandlerInterface
{
    private MailerInterface $mailer;

    private EntityRepository $userEntityRepository;

    public static function getHandledMessages(): iterable
    {
        yield PasswordChangeRequestedMessage::class => 'handlePasswordChangeRequestedMessage';
    }

    public function __construct(EntityRepository $drawUserEntityRepository, MailerInterface $mailer)
    {
        $this->userEntityRepository = $drawUserEntityRepository;
        $this->mailer = $mailer;
    }

    public function __invoke(PasswordChangeRequestedMessage $message): void
    {
        switch (true) {
            case null === $user = $this->userEntityRepository->find($message->getUserId()):
            case !$user instanceof PasswordChangeUserInterface:
            case !$user->getNeedChangePassword():
            case !method_exists($user, 'getEmail'):
            case !$user->getEmail():
                return;
        }

        $this->mailer->send((new PasswordChangeRequestedEmail())->setUserIdentifier($user->getId()));
    }
}
