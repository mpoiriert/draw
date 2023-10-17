<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataIntegrationBundle\Console\Admin\ExecutionAdmin;
use Draw\Bundle\SonataIntegrationBundle\Console\CommandRegistry;
use Draw\Bundle\SonataIntegrationBundle\Console\Controller\ExecutionController;
use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DrawSonataIntegrationExtension::class)]
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

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield [ExecutionAdmin::class];
        yield [CommandRegistry::class];
        yield [ExecutionController::class];
    }
}
