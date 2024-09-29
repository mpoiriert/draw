<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\RolesTwoFactorAuthenticationEnforcer;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\TwoFactorAuthenticationEnforcerInterface;

/**
 * @internal
 */
class DrawUserExtensionWithEnforce2faWithEnforcingRolesTest extends DrawUserExtensionWithEnforce2faTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();
        $configuration['enforce_2fa']['enforcing_roles'] = ['ROLE_ADMIN'];

        return $configuration;
    }

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield from static::removeProvidedService(
            [TwoFactorAuthenticationEnforcerInterface::class],
            parent::provideTestHasServiceDefinition()
        );
        yield [TwoFactorAuthenticationEnforcerInterface::class, RolesTwoFactorAuthenticationEnforcer::class];
        yield [RolesTwoFactorAuthenticationEnforcer::class];
    }
}
