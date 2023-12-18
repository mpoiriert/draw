<?php

namespace Draw\Bundle\UserBundle\MessageHandler;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\Email\UserOnboardingEmail;
use Draw\Bundle\UserBundle\Message\NewUserMessage;
use Draw\Component\Mailer\Recipient\LocalizationAwareInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class NewUserSendEmailMessageHandler implements MessageHandlerInterface
{
    public static function getHandledMessages(): iterable
    {
        yield NewUserMessage::class => 'handleNewUserMessage';
    }

    /**
     * @param EntityRepository<UserInterface> $drawUserEntityRepository
     */
    public function __construct(
        private EntityRepository $drawUserEntityRepository,
        private MailerInterface $mailer
    ) {
    }

    public function __invoke(NewUserMessage $message): void
    {
        $user = $this->drawUserEntityRepository->find($message->getUserId());

        if (!method_exists($user, 'getEmail') || empty($user->getEmail())) {
            return;
        }

        $this->mailer->send(
            (new UserOnboardingEmail())
                ->setUserId($message->getUserId())
                ->setLocale(
                    $user instanceof LocalizationAwareInterface ?
                        $user->getPreferredLocale() :
                        null
                )
        );
    }
}
