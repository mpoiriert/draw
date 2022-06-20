<?php

namespace Draw\Bundle\UserBundle\MessageHandler;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\Email\UserOnboardingEmail;
use Draw\Bundle\UserBundle\Message\NewUserMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class NewUserSendEmailMessageHandler implements MessageHandlerInterface
{
    private MailerInterface $mailer;

    /**
     * @var EntityRepository<UserInterface>
     */
    private EntityRepository $userEntityRepository;

    public static function getHandledMessages(): iterable
    {
        yield NewUserMessage::class => 'handleNewUserMessage';
    }

    /**
     * @param EntityRepository<UserInterface> $drawUserEntityRepository
     */
    public function __construct(EntityRepository $drawUserEntityRepository, MailerInterface $mailer)
    {
        $this->userEntityRepository = $drawUserEntityRepository;
        $this->mailer = $mailer;
    }

    public function __invoke(NewUserMessage $message): void
    {
        $user = $this->userEntityRepository->find($message->getUserId());

        if (null === $user) {
            return;
        }

        if (!method_exists($user, 'getEmail') || empty($user->getEmail())) {
            return;
        }

        $this->mailer->send((new UserOnboardingEmail())->setUserId($message->getUserId()));
    }
}
