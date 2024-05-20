<?php

namespace Draw\Component\DependencyInjection\Integration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

interface IntegrationInterface
{
    public function getConfigSectionName(): string;

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void;

    public function addConfiguration(ArrayNodeDefinition $node): void;
}
