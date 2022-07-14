<?php

namespace Draw\Bundle\UserBundle\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class TwoFactorAuthCodeEmail extends TemplatedEmail
{
    private string $authCode;

    public function __construct(string $toEmail, string $authCode)
    {
        parent::__construct();
        $this->to($toEmail);
        $this->authCode = $authCode;
    }

    public function getAuthCode(): string
    {
        return $this->authCode;
    }
}
