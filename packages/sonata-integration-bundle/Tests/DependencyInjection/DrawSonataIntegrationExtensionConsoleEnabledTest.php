<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataIntegrationBundle\Console\Admin\ExecutionAdmin;
use Draw\Bundle\SonataIntegrationBundle\Console\CommandRegistry;
use Draw\Bundle\SonataIntegrationBundle\Console\Controller\ExecutionController;

/**
 * @covers \Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension
 */
class DrawSonataIntegrationExtensionConsoleEnabledTest extends DrawSonataIntegrationExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['console'] = [
            'enabled' => true,
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield [ExecutionAdmin::class];
        yield [CommandRegistry::class];
        yield [ExecutionController::class];
    }
}
