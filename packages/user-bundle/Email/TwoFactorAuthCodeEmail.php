<?php

namespace Draw\Bundle\UserBundle\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class TwoFactorAuthCodeEmail extends TemplatedEmail
{
    public function __construct(string $toEmail, private string $authCode)
    {
        parent::__construct();
        $this->to($toEmail);
    }

    public function getAuthCode(): string
    {
        return $this->authCode;
    }
}
