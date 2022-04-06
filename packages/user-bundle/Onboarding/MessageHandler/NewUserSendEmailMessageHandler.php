<?php

namespace Draw\Bundle\UserBundle\Onboarding\MessageHandler;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\Onboarding\Email\UserOnboardingEmail;
use Draw\Bundle\UserBundle\Onboarding\Message\NewUserMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NewUserSendEmailMessageHandler implements MessageHandlerInterface
{
    private MailerInterface $mailer;

    private EntityRepository $userEntityRepository;

    public static function getHandledMessages(): iterable
    {
        yield NewUserMessage::class => 'handleNewUserMessage';
    }

    public function __construct(EntityRepository $drawUserEntityRepository, MailerInterface $mailer)
    {
        $this->userEntityRepository = $drawUserEntityRepository;
        $this->mailer = $mailer;
    }

    public function __invoke(NewUserMessage $message): void
    {
        switch (true) {
            case null === $user = $this->userEntityRepository->find($message->getUserId()):
            case !method_exists($user, 'getEmail'):
            case !$user->getEmail():
                return;
        }

        $this->mailer->send((new UserOnboardingEmail())->setUserIdentifier($message->getUserId()));
    }
}
