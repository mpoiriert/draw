<?php

namespace Draw\Bundle\UserBundle\EventListener;

use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\TwoFactorAuthenticationEnforcerInterface;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity\TwoFactorAuthenticationUserInterface;

class TwoFactorAuthenticationEntityListener
{
    public function __construct(private TwoFactorAuthenticationEnforcerInterface $twoFactorAuthenticationEnforcer)
    {
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
