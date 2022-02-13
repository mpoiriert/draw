<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer;

use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\TwoFactorAuthenticationUserInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Does not change the current configuration of the user.
 */
class RolesTwoFactorAuthenticationEnforcer implements TwoFactorAuthenticationEnforcerInterface
{
    private $roleHierarchy;

    private $enforcingRoles;

    public function __construct(RoleHierarchyInterface $roleHierarchy, array $enforcingRoles)
    {
        $this->roleHierarchy = $roleHierarchy;
        $this->enforcingRoles = $enforcingRoles;
    }

    public function shouldEnforceTwoFactorAuthentication(TwoFactorAuthenticationUserInterface $user): bool
    {
        if (!$user instanceof UserInterface) {
            return $user->isForceEnablingTwoFactorAuthentication();
        }

        $roles = $this->roleHierarchy->getReachableRoleNames($user->getRoles());

        foreach ($roles as $role) {
            if (in_array($role, $this->enforcingRoles)) {
                return true;
            }
        }

        return $user->isForceEnablingTwoFactorAuthentication();
    }
}
