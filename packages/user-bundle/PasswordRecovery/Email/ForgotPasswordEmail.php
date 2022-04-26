<?php

namespace Draw\Bundle\UserBundle\PasswordRecovery\Email;

use Draw\Component\Mailer\Email\CallToActionEmail;

class ForgotPasswordEmail extends CallToActionEmail
{
    private string $emailAddress;

    public function __construct(string $emailAddress)
    {
        parent::__construct();
        $this->emailAddress = $emailAddress;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }
}
