<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Security\Http\EventListener\RoleRestrictedAuthenticatorListener;

class DrawFrameworkExtraExtensionSecurityEnabledTest extends DrawFrameworkExtraExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['security'] = [
            'enabled' => true,
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.security.role_restricted_authenticator_listener'];
        yield [RoleRestrictedAuthenticatorListener::class, 'draw.security.role_restricted_authenticator_listener'];
    }
}
