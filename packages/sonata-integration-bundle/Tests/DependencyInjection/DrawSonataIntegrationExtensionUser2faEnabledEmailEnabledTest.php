<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

/**
 * @covers \Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension
 */
class DrawSonataIntegrationExtensionUser2faEnabledEmailEnabledTest extends DrawSonataIntegrationExtensionUser2faEnabledTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['user']['2fa']['email'] = [
            'enabled' => true,
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();

        yield ['draw.sonata.user.action.two_factor_authentication_resend_code_action'];
    }
}
