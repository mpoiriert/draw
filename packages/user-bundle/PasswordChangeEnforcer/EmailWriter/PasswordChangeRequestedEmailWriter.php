<?php

namespace Draw\Bundle\UserBundle\PasswordChangeEnforcer\EmailWriter;

use DateTimeImmutable;
use Draw\Bundle\PostOfficeBundle\EmailWriter\EmailWriterInterface;
use Draw\Bundle\UserBundle\Message\RedirectToSecuredRouteMessage;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Email\PasswordChangeRequestedEmail;
use Draw\Component\Messenger\ManuallyTriggeredMessageUrlGenerator;

class PasswordChangeRequestedEmailWriter implements EmailWriterInterface
{
    private ManuallyTriggeredMessageUrlGenerator $messageUrlGenerator;

    public static function getForEmails(): array
    {
        return ['compose' => 0];
    }

    public function __construct(ManuallyTriggeredMessageUrlGenerator $messageUrlGenerator)
    {
        $this->messageUrlGenerator = $messageUrlGenerator;
    }

    public function compose(PasswordChangeRequestedEmail $email): void
    {
        if (!$email->getHtmlTemplate()) {
            $email->htmlTemplate('@DrawUser/Email/password_change_requested_email.html.twig');
        }

        if (!$email->getCallToActionLink()) {
            $email->callToActionLink(
                $this->messageUrlGenerator->generateLink(
                    new RedirectToSecuredRouteMessage(
                        $email->getUserIdentifier(),
                        'admin_change_password'
                    ),
                    new DateTimeImmutable('+ 1 day'),
                    'change_password'
                )
            );
        }
    }
}
