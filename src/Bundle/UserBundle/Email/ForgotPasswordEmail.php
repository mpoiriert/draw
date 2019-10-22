<?php namespace Draw\Bundle\UserBundle\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ForgotPasswordEmail extends TemplatedEmail
{
    private $emailAddress;

    public function __construct(string $emailAddress)
    {
        parent::__construct();
        $this->emailAddress = $emailAddress;
        $this->htmlTemplate('@DrawUser/Email/reset_password_email.twig.html');
    }

    /**
     * The email address of the person who forgot is email
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }
}