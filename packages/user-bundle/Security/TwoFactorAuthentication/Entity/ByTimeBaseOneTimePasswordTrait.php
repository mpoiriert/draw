<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;

trait ByTimeBaseOneTimePasswordTrait
{
    use ConfigurationTrait;

    #[ORM\Column(name: 'totp_secret', type: Types::STRING, nullable: true)]
    private ?string $totpSecret = null;

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(?string $totpSecret): void
    {
        $this->totpSecret = $totpSecret;
    }

    abstract public function getUserIdentifier(): string;

    public function isTotpAuthenticationEnabled(): bool
    {
        return \in_array('totp', $this->getTwoFactorAuthenticationEnabledProviders(), true) && $this->totpSecret;
    }

    public function needToEnableTotpAuthenticationEnabled(): bool
    {
        return \in_array('totp', $this->getTwoFactorAuthenticationEnabledProviders(), true) && !$this->totpSecret;
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
