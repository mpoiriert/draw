<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataIntegrationBundle\Configuration\Admin\ConfigAdmin;

/**
 * @covers \Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension
 */
class DrawSonataIntegrationExtensionConfigurationEnabledTest extends DrawSonataIntegrationExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['configuration'] = [
            'enabled' => true,
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield [ConfigAdmin::class];
    }

    public function testConfigAdminDefinition(): void
    {
        $definition = $this->getContainerBuilder()->getDefinition(ConfigAdmin::class);

        $methodCalls = $definition->getMethodCalls();

        $this->assertSame(
            [
                ['setTranslationDomain', ['DrawConfigurationSonata']],
            ],
            $methodCalls
        );
    }
}
