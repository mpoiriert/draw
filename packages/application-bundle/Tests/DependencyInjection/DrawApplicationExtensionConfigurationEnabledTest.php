<?php

namespace Draw\Bundle\ApplicationBundle\Tests\DependencyInjection;

use Draw\Bundle\ApplicationBundle\Configuration\Repository\ConfigRepository;
use Draw\Bundle\ApplicationBundle\DependencyInjection\DrawApplicationExtension;
use Draw\Contracts\Application\ConfigurationRegistryInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawApplicationExtensionConfigurationEnabledTest extends DrawApplicationExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawApplicationExtension();
    }

    public function getConfiguration(): array
    {
        return [
            'configuration' => [
                'enabled' => true,
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [ConfigRepository::class];
        yield [ConfigurationRegistryInterface::class, ConfigRepository::class];
    }
}
