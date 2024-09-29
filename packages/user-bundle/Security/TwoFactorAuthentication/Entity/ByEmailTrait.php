<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ByEmailTrait
{
    use ConfigurationTrait;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $emailAuthCode = null;

    #[ORM\Column(name: 'email_auth_code_generated_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $emailAuthCodeGeneratedAt = null;

    public function isEmailAuthEnabled(): bool
    {
        return \in_array('email', $this->getTwoFactorAuthenticationEnabledProviders(), true) && $this->getEmailAuthRecipient();
    }

    public function getEmailAuthRecipient(): string
    {
        if (method_exists($this, 'getEmail')) {
            return (string) $this->getEmail();
        }

        throw new \RuntimeException('You must override the trait method getEmailAuthRecipient or implement getEmail');
    }

    public function getEmailAuthCode(): string
    {
        if (null === $this->emailAuthCode || $this->isEmailAuthCodeGeneratedExpired()) {
            throw new \LogicException('The email authentication code was not set');
        }

        return $this->emailAuthCode;
    }

    public function setEmailAuthCode(string $authCode): void
    {
        $this->emailAuthCode = $authCode;

        $this->emailAuthCodeGeneratedAt = new \DateTimeImmutable();
    }

    protected function isEmailAuthCodeGeneratedExpired(): bool
    {
        return false;
    }
}
