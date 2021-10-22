<?php

namespace Draw\Bundle\UserBundle\Email;

use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ForgotPasswordEmail extends TemplatedEmail
{
    private $emailAddress;

    /**
     * @var SecurityUserInterface
     */
    private $user;

    /**
     * @var string
     */
    private $callToActionLink;

    public function __construct(string $emailAddress)
    {
        parent::__construct();
        $this->emailAddress = $emailAddress;
        $this->htmlTemplate('@DrawUser/Email/reset_password_email.html.twig');
    }

    public function getContext(): array
    {
        $context = parent::getContext();
        $extraContexts[] = [
            'email_address' => $this->emailAddress,
            'call_to_action_link' => $this->callToActionLink,
        ];
        if ($this->user) {
            $extraContexts[] = ['user' => $this->user];
        }

        return array_merge($context, ...$extraContexts);
    }

    /**
     * @return SecurityUserInterface
     */
    public function getUser(): ?SecurityUserInterface
    {
        return $this->user;
    }

    public function user(SecurityUserInterface $user): ForgotPasswordEmail
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getCallToActionLink(): ?string
    {
        return $this->callToActionLink;
    }

    public function callToActionLink(string $callToActionLink): ForgotPasswordEmail
    {
        $this->callToActionLink = $callToActionLink;

        return $this;
    }

    /**
     * The email address of the person who forgot is email.
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }
}
