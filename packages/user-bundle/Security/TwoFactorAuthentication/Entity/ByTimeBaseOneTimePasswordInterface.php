<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity;

use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;

interface ByTimeBaseOneTimePasswordInterface extends TwoFactorInterface, TwoFactorAuthenticationUserInterface
{
    public function getTotpSecret(): ?string;

    public function setTotpSecret(?string $totpSecret): void;
}
