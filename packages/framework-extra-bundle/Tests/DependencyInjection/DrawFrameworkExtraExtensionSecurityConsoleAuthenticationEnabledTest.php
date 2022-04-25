<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Security\Core\Listener\CommandLineAuthenticatorListener;

class DrawFrameworkExtraExtensionSecurityConsoleAuthenticationEnabledTest extends DrawFrameworkExtraExtensionSecurityEnabledTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['security']['console_authentication'] = [
            'enabled' => true,
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.security.command_line_authenticator_listener'];
        yield [CommandLineAuthenticatorListener::class, 'draw.security.command_line_authenticator_listener'];
    }
}
