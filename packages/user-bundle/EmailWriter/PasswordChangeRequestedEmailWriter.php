<?php

namespace Draw\Bundle\UserBundle\EmailWriter;

use Draw\Bundle\UserBundle\Email\PasswordChangeRequestedEmail;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Message\RedirectToSecuredRouteMessage;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Draw\Component\Messenger\ManualTrigger\ManuallyTriggeredMessageUrlGenerator;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class PasswordChangeRequestedEmailWriter implements EmailWriterInterface
{
    private ManuallyTriggeredMessageUrlGenerator $messageUrlGenerator;

    private UserProviderInterface $userProvider;

    public static function getForEmails(): array
    {
        return ['compose' => 0];
    }

    public function __construct(
        ManuallyTriggeredMessageUrlGenerator $messageUrlGenerator,
        UserProviderInterface $userProvider
    ) {
        $this->messageUrlGenerator = $messageUrlGenerator;
        $this->userProvider = $userProvider;
    }

    public function compose(PasswordChangeRequestedEmail $email): void
    {
        if (!$email->getHtmlTemplate()) {
            $email->htmlTemplate('@DrawUser/Email/password_change_requested_email.html.twig');
        }

        if (!$email->getCallToActionLink()) {
            $user = $this->userProvider->loadUserByIdentifier($email->getUserIdentifier());

            $parameters = [];
            if ($user instanceof SecurityUserInterface) {
                $parameters['t'] = $user->getPasswordUpdatedAt() ? $user->getPasswordUpdatedAt()->getTimestamp() : 0;
            }

            $email->callToActionLink(
                $this->messageUrlGenerator->generateLink(
                    new RedirectToSecuredRouteMessage(
                        $email->getUserIdentifier(),
                        'admin_change_password',
                        $parameters
                    ),
                    new \DateTimeImmutable('+ 1 day'),
                    'change_password'
                )
            );
        }
    }
}
