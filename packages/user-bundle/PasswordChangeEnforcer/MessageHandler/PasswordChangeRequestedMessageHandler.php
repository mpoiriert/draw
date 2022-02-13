<?php

namespace Draw\Bundle\UserBundle\PasswordChangeEnforcer\MessageHandler;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Email\PasswordChangeRequestedEmail;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Entity\PasswordChangeUserInterface;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Message\PasswordChangeRequestedMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class PasswordChangeRequestedMessageHandler implements MessageSubscriberInterface
{
    private $mailer;

    private $userEntityRepository;

    public static function getHandledMessages(): iterable
    {
        yield PasswordChangeRequestedMessage::class => 'handlePasswordChangeRequestedMessage';
    }

    public function __construct(EntityRepository $userEntityRepository, MailerInterface $mailer)
    {
        $this->userEntityRepository = $userEntityRepository;
        $this->mailer = $mailer;
    }

    public function handlePasswordChangeRequestedMessage(PasswordChangeRequestedMessage $message): void
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
