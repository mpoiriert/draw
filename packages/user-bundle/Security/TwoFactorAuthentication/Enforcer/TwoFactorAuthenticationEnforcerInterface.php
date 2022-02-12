<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer;

use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\TwoFactorAuthenticationUserInterface;

interface TwoFactorAuthenticationEnforcerInterface
{
    public function shouldEnforceTwoFactorAuthentication(TwoFactorAuthenticationUserInterface $user): bool;
}
