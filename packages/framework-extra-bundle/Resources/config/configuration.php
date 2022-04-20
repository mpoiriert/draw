<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Draw\Component\Application\DoctrineConfigurationRegistry;
use Draw\Contracts\Application\ConfigurationRegistryInterface;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        ->defaults()
            ->autoconfigure()
            ->autowire()

        ->set('draw.configuration.doctrine_configuration_repository', DoctrineConfigurationRegistry::class)
        ->alias(DoctrineConfigurationRegistry::class, 'draw.configuration.doctrine_configuration_repository')
        ->alias(ConfigurationRegistryInterface::class, 'draw.configuration.doctrine_configuration_repository');
};
