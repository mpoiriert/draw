<?php

namespace Draw\Bundle\UserBundle\Email;

use Draw\Bundle\PostOfficeBundle\Email\CallToActionEmail;

class ForgotPasswordEmail extends CallToActionEmail
{
    private $emailAddress;

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
