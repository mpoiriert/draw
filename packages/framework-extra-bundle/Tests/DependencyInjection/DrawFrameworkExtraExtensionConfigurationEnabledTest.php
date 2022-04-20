<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Application\DoctrineConfigurationRegistry;
use Draw\Contracts\Application\ConfigurationRegistryInterface;

class DrawFrameworkExtraExtensionConfigurationEnabledTest extends DrawFrameworkExtraExtensionTest
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
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.configuration.doctrine_configuration_repository'];
        yield [DoctrineConfigurationRegistry::class, 'draw.configuration.doctrine_configuration_repository'];
        yield [ConfigurationRegistryInterface::class, 'draw.configuration.doctrine_configuration_repository'];
    }
}
