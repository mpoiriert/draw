<?php

namespace Draw\Component\DependencyInjection\DependencyInjection;

use Draw\Component\DependencyInjection\DependencyInjection\Compiler\TagIfExpressionCompilerPass;
use Draw\Component\DependencyInjection\Integration\ContainerBuilderIntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class DependencyInjectionIntegration implements IntegrationInterface, ContainerBuilderIntegrationInterface
{
    public function getConfigSectionName(): string
    {
        return 'dependency_injection';
    }

    public function buildContainer(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TagIfExpressionCompilerPass());
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
    }
}
