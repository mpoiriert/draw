<?php

namespace Draw\Bundle\UserBundle\PasswordChangeEnforcer\Email;

use Draw\Bundle\UserBundle\Email\ForgotPasswordEmail;

class PasswordChangeRequestedEmail extends ForgotPasswordEmail
{
    public function __construct(string $emailAddress)
    {
        parent::__construct($emailAddress);
        $this->htmlTemplate('@DrawUser/Email/password_change_requested_email.html.twig');
    }
}
