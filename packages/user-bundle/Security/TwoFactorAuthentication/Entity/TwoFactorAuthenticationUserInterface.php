<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity;

interface TwoFactorAuthenticationUserInterface
{
    public function getTwoFactorAuthenticationEnabledProviders(): array;

    public function setTwoFactorAuthenticationEnabledProviders(array $providers): void;

    public function enableTwoFActorAuthenticationProvider(string $provider): void;

    public function disableTwoFActorAuthenticationProvider(string $provider): void;

    public function asOneTwoFActorAuthenticationProviderEnabled(): bool;

    public function isForceEnablingTwoFactorAuthentication(): bool;

    public function setForceEnablingTwoFactorAuthentication(bool $forceEnablingTwoFactorAuthentication): void;
}
