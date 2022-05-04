<?php

namespace Draw\Bundle\UserBundle\EventListener;

use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\TwoFactorAuthenticationEnforcerInterface;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\TwoFactorAuthenticationUserInterface;

class TwoFactorAuthenticationEntityListener
{
    private TwoFactorAuthenticationEnforcerInterface $twoFactorAuthenticationEnforcer;

    public function __construct(TwoFactorAuthenticationEnforcerInterface $twoFactorAuthenticationEnforcer)
    {
        $this->twoFactorAuthenticationEnforcer = $twoFactorAuthenticationEnforcer;
    }

    public function preUpdate(TwoFactorAuthenticationUserInterface $user): void
    {
        $this->setForceEnablingTwoFactorAuthentication($user);
    }

    public function prePersist(TwoFactorAuthenticationUserInterface $user): void
    {
        $this->setForceEnablingTwoFactorAuthentication($user);
    }

    private function setForceEnablingTwoFactorAuthentication(TwoFactorAuthenticationUserInterface $user): void
    {
        $user->setForceEnablingTwoFactorAuthentication(
            $this->twoFactorAuthenticationEnforcer->shouldEnforceTwoFactorAuthentication($user)
        );
    }
}
