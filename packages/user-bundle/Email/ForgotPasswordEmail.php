<?php

namespace Draw\Bundle\UserBundle\Email;

use Draw\Component\Mailer\Email\CallToActionEmail;

class ForgotPasswordEmail extends CallToActionEmail
{
    public function __construct(private string $emailAddress)
    {
        parent::__construct();
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }
}
