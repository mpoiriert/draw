<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ConfigurationTrait
{
    /**
     * @var string[]
     *
     * @ORM\Column(name="two_factor_authentication_enabled_providers", type="json", nullable=true)
     */
    private ?array $twoFactorAuthenticationEnabledProviders = [];

    /**
     * @ORM\Column(name="force_enabling_two_factor_authentication", type="boolean", nullable=false, options={"default":"0"})
     */
    private bool $forceEnablingTwoFactorAuthentication = false;

    public function getTwoFactorAuthenticationEnabledProviders(): array
    {
        return $this->twoFactorAuthenticationEnabledProviders ?? $this->twoFactorAuthenticationEnabledProviders = [];
    }

    public function setTwoFactorAuthenticationEnabledProviders(array $providers): void
    {
        $this->twoFactorAuthenticationEnabledProviders = array_values($providers);
    }

    public function enableTwoFActorAuthenticationProvider(string $provider): void
    {
        $enabledProviders = $this->getTwoFactorAuthenticationEnabledProviders();

        if (!\in_array($enabledProviders, $this->twoFactorAuthenticationEnabledProviders)) {
            $enabledProviders[] = $provider;

            $this->setTwoFactorAuthenticationEnabledProviders($enabledProviders);
        }
    }

    public function disableTwoFActorAuthenticationProvider(string $provider): void
    {
        $this->setTwoFactorAuthenticationEnabledProviders(array_diff($this->getTwoFactorAuthenticationEnabledProviders(), [$provider]));
    }

    public function asOneTwoFActorAuthenticationProviderEnabled(): bool
    {
        if ($this instanceof ByEmailInterface && $this->isEmailAuthEnabled()) {
            return true;
        }

        if ($this instanceof ByTimeBaseOneTimePasswordInterface && $this->isTotpAuthenticationEnabled()) {
            return true;
        }

        return false;
    }

    public function isForceEnablingTwoFactorAuthentication(): bool
    {
        return $this->forceEnablingTwoFactorAuthentication;
    }

    public function setForceEnablingTwoFactorAuthentication(bool $forceEnablingTwoFactorAuthentication): void
    {
        $this->forceEnablingTwoFactorAuthentication = $forceEnablingTwoFactorAuthentication;
    }
}
