<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\IndecisiveTwoFactorAuthenticationEnforcer;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\RolesTwoFactorAuthenticationEnforcer;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\TwoFactorAuthenticationEnforcerInterface;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Listener\TwoFactorAuthenticationEntityListener;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Listener\TwoFactorAuthenticationSubscriber;
use Draw\Bundle\UserBundle\Tests\Fixtures\Entity\User;

class DrawUserExtensionWithEnforce2faWithEnforcingRolesTest extends DrawUserExtensionTest
{
    public function getConfiguration(): array
    {
        return [
            'user_entity_class' => User::class,
            'enforce_2fa' => [
                'enabled' => true,
                'enforcing_roles' => ['ROLE_ADMIN'],
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [TwoFactorAuthenticationEntityListener::class];
        yield [TwoFactorAuthenticationSubscriber::class];
        yield [TwoFactorAuthenticationEnforcerInterface::class, RolesTwoFactorAuthenticationEnforcer::class];
        yield [IndecisiveTwoFactorAuthenticationEnforcer::class];
        yield [RolesTwoFactorAuthenticationEnforcer::class];
    }
}
