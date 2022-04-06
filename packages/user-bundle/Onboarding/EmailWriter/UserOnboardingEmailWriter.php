<?php

namespace Draw\Bundle\UserBundle\Onboarding\EmailWriter;

use DateTimeImmutable;
use Draw\Bundle\MessengerBundle\CallToAction\MessageUrlGenerator;
use Draw\Bundle\PostOfficeBundle\EmailWriter\EmailWriterInterface;
use Draw\Bundle\UserBundle\Message\RedirectToSecuredRouteMessage;
use Draw\Bundle\UserBundle\Onboarding\Email\UserOnboardingEmail;

class UserOnboardingEmailWriter implements EmailWriterInterface
{
    private MessageUrlGenerator $messageUrlGenerator;

    private string $messageExpirationDelay;

    public static function getForEmails(): array
    {
        return ['compose' => 0];
    }

    public function __construct(
        MessageUrlGenerator $messageUrlGenerator,
        string $messageExpirationDelay = '+ 7 days'
    ) {
        $this->messageExpirationDelay = $messageExpirationDelay;
        $this->messageUrlGenerator = $messageUrlGenerator;
    }

    public function compose(UserOnboardingEmail $email): void
    {
        if (!$email->getHtmlTemplate()) {
            $email->htmlTemplate('@DrawUser/Email/user_onboarding_email.html.twig');
        }

        if (!$email->getCallToActionLink()) {
            $email->callToActionLink(
                $this->messageUrlGenerator->generateLink(
                    new RedirectToSecuredRouteMessage(
                        $email->getUserIdentifier(),
                        'draw_user_account_confirmation'
                    ),
                    new DateTimeImmutable($this->messageExpirationDelay),
                    'account_confirmation'
                )
            );
        }
    }
}
