<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Listener;

use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\TwoFactorAuthenticationEnforcerInterface;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\TwoFactorAuthenticationUserInterface;

class TwoFactorAuthenticationEntityListener
{
    private $twoFactorAuthenticationEnforcer;

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