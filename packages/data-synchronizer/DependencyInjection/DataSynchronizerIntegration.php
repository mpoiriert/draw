<?php

namespace Draw\Component\DataSynchronizer\DependencyInjection;

use Draw\Component\DataSynchronizer\DataSynchronizer;
use Draw\Component\DataSynchronizer\Serializer\SerializerFactory;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationTrait;
use JMS\Serializer\Construction\UnserializeObjectConstructor;
use JMS\Serializer\Serializer;
use Metadata\Driver\FileLocator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Parameter;

class DataSynchronizerIntegration implements IntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'data_synchronizer';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->registerClasses(
            $loader,
            $namespace = 'Draw\Component\DataSynchronizer\\',
            \dirname((new \ReflectionClass(DataSynchronizer::class))->getFileName()),
        );

        $container
            ->setParameter('draw.data_synchronizer.metadata_directory', $config['metadata_directory'])
        ;

        $container
            ->setDefinition(
                'draw.data_synchronizer.serializer.construction.default',
                new Definition(UnserializeObjectConstructor::class)
            )
        ;

        $container
            ->setDefinition(
                'draw.data_synchronizer.serializer.metadata.file_locator',
                (new Definition(FileLocator::class))
                    ->setArgument(
                        0,
                        [
                            '' => new Parameter('draw.data_synchronizer.metadata_directory'),
                        ]
                    )
            )
        ;

        $container
            ->setDefinition(
                'draw.data_synchronizer.serializer',
                (new Definition(Serializer::class))
                    ->setAutoconfigured(true)
                    ->setAutowired(true)
                    ->setFactory([SerializerFactory::class, 'create'])
            )
        ;

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.data_synchronizer.'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->scalarNode('metadata_directory')
                    ->defaultValue('%kernel.project_dir%/config/data-synchronizer/metadata')
                ->end()
            ->end()
        ;
    }
}
