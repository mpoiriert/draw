<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication;

use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;

interface TwoFactorAuthenticationUserInterface extends TwoFactorInterface
{
    public function getTotpSecret(): ?string;

    public function setTotpSecret(?string $totpSecret): void;

    public function isForceEnablingTwoFactorAuthentication(): bool;

    public function setForceEnablingTwoFactorAuthentication(bool $forceEnablingTwoFactorAuthentication): void;
}
