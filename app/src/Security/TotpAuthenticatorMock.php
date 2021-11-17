<?php

namespace App\Security;

use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;

class TotpAuthenticatorMock implements TotpAuthenticatorInterface
{
    public function checkCode(TwoFactorInterface $user, string $code): bool
    {
        return '123456' === $code;
    }

    public function getQRContent(TwoFactorInterface $user): string
    {
        return $user->getTotpAuthenticationUsername();
    }

    public function generateSecret(): string
    {
        return 'TEST_SECRET';
    }
}
