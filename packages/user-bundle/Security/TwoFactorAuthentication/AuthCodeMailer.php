<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication;

use Draw\Bundle\UserBundle\Email\TwoFactorAuthCodeEmail;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Component\Mailer\MailerInterface;

class AuthCodeMailer implements AuthCodeMailerInterface
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function sendAuthCode(TwoFactorInterface $user): void
    {
        $this->mailer->send(
            (new TwoFactorAuthCodeEmail($user->getEmailAuthRecipient(), $user->getEmailAuthCode()))
                ->htmlTemplate('@DrawUser/Email/2fa_auth_code_email.html.twig')
                ->context(['auth_code' => $user->getEmailAuthCode()])
        );
    }
}
