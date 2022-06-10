<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Draw\Component\Process\ProcessFactory;
use Draw\Contracts\Process\ProcessFactoryInterface;
use ReflectionClass;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class ProcessIntegration implements IntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'process';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $directory = dirname(
            (new ReflectionClass(ProcessFactory::class))->getFileName(),
        );

        $definition = (new Definition())
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $exclude = [
            $directory.'/Tests',
        ];

        $namespace = 'Draw\\Component\\Process\\';

        $loader->registerClasses(
            $definition,
            $namespace,
            $directory,
            $exclude
        );

        $container->setAlias(ProcessFactoryInterface::class, ProcessFactory::class);

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.process.'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        // nothing to do
    }
}
