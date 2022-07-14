<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity;

interface TwoFactorAuthenticationUserInterface
{
    public function getEnabledProviders(): array;

    public function setEnabledProviders(array $providers): void;

    public function enableProvider(string $provider): void;

    public function disableProvider(string $provider): void;

    public function asOneProviderEnabled(): bool;

    public function isForceEnablingTwoFactorAuthentication(): bool;

    public function setForceEnablingTwoFactorAuthentication(bool $forceEnablingTwoFactorAuthentication): void;
}
