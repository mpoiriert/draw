<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer;

use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity\TwoFactorAuthenticationUserInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Enforce base on a list roles. Does not change the current user configuration if not roles get match.
 */
class RolesTwoFactorAuthenticationEnforcer implements TwoFactorAuthenticationEnforcerInterface
{
    private RoleHierarchyInterface $roleHierarchy;

    /**
     * @var string[]
     */
    private array $enforcingRoles;

    /**
     * @param string[] $enforcingRoles
     */
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
            if (\in_array($role, $this->enforcingRoles)) {
                return true;
            }
        }

        return $user->isForceEnablingTwoFactorAuthentication();
    }
}
