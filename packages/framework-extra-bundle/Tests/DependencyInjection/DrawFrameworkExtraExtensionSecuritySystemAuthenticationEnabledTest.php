<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Security\Core\Authentication\SystemAuthenticatorInterface;

class DrawFrameworkExtraExtensionSecuritySystemAuthenticationEnabledTest extends DrawFrameworkExtraExtensionSecurityEnabledTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['security']['system_authentication'] = [
            'enabled' => true,
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.security.system_authentication'];
        yield [SystemAuthenticatorInterface::class, 'draw.security.system_authentication'];
    }
}
