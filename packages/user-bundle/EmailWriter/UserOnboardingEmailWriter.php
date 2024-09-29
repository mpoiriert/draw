<?php

namespace Draw\Bundle\UserBundle\EmailWriter;

use Draw\Bundle\UserBundle\Email\UserOnboardingEmail;
use Draw\Bundle\UserBundle\Message\RedirectToSecuredRouteMessage;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Draw\Component\Messenger\ManualTrigger\ManuallyTriggeredMessageUrlGenerator;

class UserOnboardingEmailWriter implements EmailWriterInterface
{
    public static function getForEmails(): array
    {
        return ['compose' => 0];
    }

    public function __construct(
        private ManuallyTriggeredMessageUrlGenerator $messageUrlGenerator,
        private string $messageExpirationDelay = '+ 7 days',
    ) {
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
                        $email->getUserId(),
                        'draw_user_account_confirmation'
                    ),
                    new \DateTimeImmutable($this->messageExpirationDelay),
                    'account_confirmation'
                )
            );
        }
    }
}
