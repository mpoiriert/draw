<?php

namespace Draw\Bundle\UserBundle\PasswordChangeEnforcer\MessageHandler;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Email\PasswordChangeRequestedEmail;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Entity\PasswordChangeUserInterface;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Message\PasswordChangeRequestedMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PasswordChangeRequestedSendEmailMessageHandler implements MessageHandlerInterface
{
    private $mailer;

    private $userEntityRepository;

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
            case !($email = $user->getEmail()):
                return;
        }

        $this->mailer->send(new PasswordChangeRequestedEmail($email));
    }
}
