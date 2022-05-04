<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @covers \Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension
 */
class DrawSonataIntegrationExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawSonataIntegrationExtension();
    }

    public function getConfiguration(): array
    {
        return [
            'configuration' => [
                'enabled' => false,
            ],
            'console' => [
                'enabled' => false,
            ],
            'messenger' => [
                'enabled' => false,
            ],
            'user' => [
                'enabled' => false,
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        return [];
    }
}
