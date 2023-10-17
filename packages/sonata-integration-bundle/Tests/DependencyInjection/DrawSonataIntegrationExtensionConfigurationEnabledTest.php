<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataIntegrationBundle\Configuration\Admin\ConfigAdmin;
use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DrawSonataIntegrationExtension::class)]
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

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield [ConfigAdmin::class];
    }

    public function testConfigAdminDefinition(): void
    {
        $definition = $this->getContainerBuilder()->getDefinition(ConfigAdmin::class);

        $methodCalls = $definition->getMethodCalls();

        static::assertSame(
            [
                ['setTranslationDomain', ['DrawConfigurationSonata']],
            ],
            $methodCalls
        );
    }
}
