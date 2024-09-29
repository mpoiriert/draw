<?php

namespace Draw\Bundle\UserBundle\MessageHandler;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\Email\UserOnboardingEmail;
use Draw\Bundle\UserBundle\Message\NewUserMessage;
use Draw\Component\Mailer\Recipient\LocalizationAwareInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\User\UserInterface;

class NewUserSendEmailMessageHandler
{
    /**
     * @param EntityRepository<UserInterface> $drawUserEntityRepository
     */
    public function __construct(
        private EntityRepository $drawUserEntityRepository,
        private MailerInterface $mailer,
    ) {
    }

    #[AsMessageHandler]
    public function handleNewUserMessage(NewUserMessage $message): void
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
