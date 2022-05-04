<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\RolesTwoFactorAuthenticationEnforcer;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\TwoFactorAuthenticationEnforcerInterface;

class DrawUserExtensionWithEnforce2faWithEnforcingRolesTest extends DrawUserExtensionWithEnforce2faTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();
        $configuration['enforce_2fa']['enforcing_roles'] = ['ROLE_ADMIN'];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from $this->removeProvidedService(
            [TwoFactorAuthenticationEnforcerInterface::class],
            parent::provideTestHasServiceDefinition()
        );
        yield [TwoFactorAuthenticationEnforcerInterface::class, RolesTwoFactorAuthenticationEnforcer::class];
        yield [RolesTwoFactorAuthenticationEnforcer::class];
    }
}
