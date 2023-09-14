<?php

namespace Draw\Bundle\UserBundle\EmailWriter;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\Email\ForgotPasswordEmail;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Message\RedirectToSecuredRouteMessage;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Draw\Component\Messenger\ManualTrigger\ManuallyTriggeredMessageUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ForgotPasswordEmailWriter implements EmailWriterInterface
{
    public static function getForEmails(): array
    {
        return ['compose' => 255];
    }

    /**
     * @param EntityRepository<SecurityUserInterface> $drawUserEntityRepository
     */
    public function __construct(
        private EntityRepository $drawUserEntityRepository,
        private ManuallyTriggeredMessageUrlGenerator $messageUrlGenerator,
        private UrlGeneratorInterface $urlGenerator,
        private string $resetPasswordRoute,
        private string $inviteCreateAccountRoute
    ) {
    }

    public function compose(ForgotPasswordEmail $forgotPasswordEmail): void
    {
        $email = $forgotPasswordEmail->getEmailAddress();

        /** @var ?SecurityUserInterface $user */
        $user = $this->drawUserEntityRepository->findOneBy(['email' => $email]);

        $this->completeEmail($forgotPasswordEmail, $user);
    }

    public function completeEmail(ForgotPasswordEmail $forgotPasswordEmail, ?SecurityUserInterface $user): void
    {
        $forgotPasswordEmail
            ->to($forgotPasswordEmail->getEmailAddress());

        if (null === $user) {
            $forgotPasswordEmail
                ->htmlTemplate('@DrawUser/Email/reset_password_email_user_not_found.html.twig')
                ->callToActionLink(
                    $this->urlGenerator->generate(
                        $this->inviteCreateAccountRoute,
                        [],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                );

            return;
        }

        $forgotPasswordEmail
            ->htmlTemplate('@DrawUser/Email/reset_password_email.html.twig')
            ->callToActionLink(
                $this->messageUrlGenerator->generateLink(
                    new RedirectToSecuredRouteMessage(
                        $user->getId(),
                        $this->resetPasswordRoute,
                        ['t' => time()]
                    ),
                    new \DateTimeImmutable('+ 1 day'),
                    'reset_password',
                )
            );
    }
}
