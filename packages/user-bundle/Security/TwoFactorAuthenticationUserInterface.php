<?php

namespace Draw\Bundle\UserBundle\Security;

use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;

interface TwoFactorAuthenticationUserInterface extends TwoFactorInterface
{
    public function getTotpSecret(): ?string;

    public function setTotpSecret(?string $totpSecret): void;
}
