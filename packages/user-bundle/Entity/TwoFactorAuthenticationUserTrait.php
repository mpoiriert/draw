<?php

namespace Draw\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;

trait TwoFactorAuthenticationUserTrait
{
    /**
     * @ORM\Column(name="totp_secret", type="string", nullable=true)
     */
    private ?string $totpSecret = null;

    /**
     * @ORM\Column(name="force_enabling_two_factor_authentication", type="boolean", nullable=false, options={"default":"0"})
     */
    private bool $forceEnablingTwoFactorAuthentication = false;

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(?string $totpSecret): void
    {
        $this->totpSecret = $totpSecret;
    }

    public function isForceEnablingTwoFactorAuthentication(): bool
    {
        return $this->forceEnablingTwoFactorAuthentication;
    }

    public function setForceEnablingTwoFactorAuthentication(bool $forceEnablingTwoFactorAuthentication): void
    {
        $this->forceEnablingTwoFactorAuthentication = $forceEnablingTwoFactorAuthentication;
    }

    abstract public function getUserIdentifier(): ?string;

    public function isTotpAuthenticationEnabled(): bool
    {
        return (bool) $this->totpSecret;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getTotpAuthenticationConfiguration(): TotpConfigurationInterface
    {
        // You could persist the other configuration options in the user entity to make it individual per user.
        return new TotpConfiguration($this->totpSecret, TotpConfiguration::ALGORITHM_SHA1, 30, 6);
    }
}
