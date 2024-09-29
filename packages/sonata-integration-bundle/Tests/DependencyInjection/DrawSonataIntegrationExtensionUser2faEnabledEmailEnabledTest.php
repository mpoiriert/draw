<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DrawSonataIntegrationExtension::class)]
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

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();

        yield ['draw.sonata.user.action.two_factor_authentication_resend_code_action'];
    }
}
