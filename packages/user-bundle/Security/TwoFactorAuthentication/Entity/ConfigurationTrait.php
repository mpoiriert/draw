<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ConfigurationTrait
{
    /**
     * @var string[]
     *
     * @ORM\Column(name="enabled_providers", type="json", nullable=true)
     */
    private ?array $enabledProviders = [];

    /**
     * @ORM\Column(name="force_enabling_two_factor_authentication", type="boolean", nullable=false, options={"default":"0"})
     */
    private bool $forceEnablingTwoFactorAuthentication = false;

    public function getEnabledProviders(): array
    {
        return $this->enabledProviders ?? $this->enabledProviders = [];
    }

    public function setEnabledProviders(array $providers): void
    {
        $this->enabledProviders = array_values($providers);
    }

    public function enableProvider(string $provider): void
    {
        $enabledProviders = $this->getEnabledProviders();

        if (!\in_array($enabledProviders, $this->enabledProviders)) {
            $enabledProviders[] = $provider;

            $this->setEnabledProviders($enabledProviders);
        }
    }

    public function asOneProviderEnabled(): bool
    {
        if ($this instanceof ByEmailInterface && $this->isEmailAuthEnabled()) {
            return true;
        }

        if ($this instanceof ByTimeBaseOneTimePasswordInterface && $this->isTotpAuthenticationEnabled()) {
            return true;
        }

        return false;
    }

    public function disableProvider(string $provider): void
    {
        $this->setEnabledProviders(array_diff($this->getEnabledProviders(), [$provider]));
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
