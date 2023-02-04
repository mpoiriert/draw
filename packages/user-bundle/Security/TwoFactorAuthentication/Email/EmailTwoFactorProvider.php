<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Email;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\EmailTwoFactorProvider as BaseEmailTwoFactorProvider;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;

class EmailTwoFactorProvider implements TwoFactorProviderInterface
{
    public function __construct(private BaseEmailTwoFactorProvider $decorated)
    {
    }

    public function beginAuthentication(AuthenticationContextInterface $context): bool
    {
        return $this->decorated->beginAuthentication($context);
    }

    public function prepareAuthentication($user): void
    {
        $this->decorated->prepareAuthentication($user);
    }

    public function validateAuthenticationCode($user, string $authenticationCode): bool
    {
        try {
            return $this->decorated->validateAuthenticationCode($user, $authenticationCode);
        } catch (\Throwable) {
            return false;
        }
    }

    public function getFormRenderer(): TwoFactorFormRendererInterface
    {
        return $this->decorated->getFormRenderer();
    }
}
