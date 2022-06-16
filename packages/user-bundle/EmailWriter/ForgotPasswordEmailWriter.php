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
    private ManuallyTriggeredMessageUrlGenerator $messageUrlGenerator;

    private EntityRepository $userEntityRepository;

    private string $resetPasswordRoute;

    private string $inviteCreateAccountRoute;

    private UrlGeneratorInterface $urlGenerator;

    public static function getForEmails(): array
    {
        return ['compose' => 255];
    }

    public function __construct(
        EntityRepository $drawUserEntityRepository,
        ManuallyTriggeredMessageUrlGenerator $messageUrlGenerator,
        UrlGeneratorInterface $urlGenerator,
        string $resetPasswordRoute,
        string $inviteCreateAccountRoute
    ) {
        $this->messageUrlGenerator = $messageUrlGenerator;
        $this->urlGenerator = $urlGenerator;
        $this->inviteCreateAccountRoute = $inviteCreateAccountRoute;
        $this->resetPasswordRoute = $resetPasswordRoute;
        $this->userEntityRepository = $drawUserEntityRepository;
    }

    public function compose(ForgotPasswordEmail $forgotPasswordEmail)
    {
        $forgotPasswordEmail
            ->to($email = $forgotPasswordEmail->getEmailAddress());

        /** @var SecurityUserInterface $user */
        $user = $this->userEntityRepository
            ->findOneBy(['email' => $email]);

        if (!$user) {
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
