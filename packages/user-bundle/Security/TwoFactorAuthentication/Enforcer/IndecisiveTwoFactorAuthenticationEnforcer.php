<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer;

use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\TwoFactorAuthenticationUserInterface;

/**
 * Does not change the current configuration of the user.
 */
class IndecisiveTwoFactorAuthenticationEnforcer implements TwoFactorAuthenticationEnforcerInterface
{
    public function shouldEnforceTwoFactorAuthentication(TwoFactorAuthenticationUserInterface $user): bool
    {
        return $user->isForceEnablingTwoFactorAuthentication();
    }
}
