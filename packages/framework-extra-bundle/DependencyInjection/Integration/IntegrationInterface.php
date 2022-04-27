<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

interface IntegrationInterface
{
    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void;

    public function getConfigSectionName(): string;
}
