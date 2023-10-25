<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Draw\Component\EntityMigrator\Entity\EntityMigrationInterface;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\Message\MigrateEntityCommand;
use Draw\Component\EntityMigrator\MigrationInterface;
use Draw\Component\EntityMigrator\Migrator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class EntityMigratorIntegration implements IntegrationInterface, PrependIntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'entity_migrator';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(MigrationInterface::class)
            ->addTag(MigrationInterface::class);

        $this->registerClasses(
            $loader,
            $namespace = 'Draw\\Component\\EntityMigrator\\',
            \dirname((new \ReflectionClass(Migrator::class))->getFileName()),
        );

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.entity_migrator.'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->scalarNode('transport')->defaultValue('async_low_priority')->end()
            ->end();
    }

    public function prepend(ContainerBuilder $container, array $config): void
    {
        $this->assertHasExtension($container, 'doctrine');

        if ($container->hasExtension('monolog')) {
            $container->prependExtensionConfig(
                'monolog',
                [
                    'channels' => ['entity_migrator'],
                ]
            );
        }

        $reflection = new \ReflectionClass(Migration::class);

        $container->prependExtensionConfig(
            'framework',
            [
                'lock' => [
                    'entity_migrator' => [
                        '%env(ENTITY_MIGRATOR_LOCK_DSN)%',
                    ],
                ],
            ]
        );

        $container->prependExtensionConfig(
            'framework',
            [
                'messenger' => [
                    'routing' => [
                        MigrateEntityCommand::class => $config['transport'],
                    ],
                ],
            ]
        );

        $container->prependExtensionConfig(
            'doctrine',
            [
                'orm' => [
                    'mappings' => [
                        'DrawEntityMigrator' => [
                            'is_bundle' => false,
                            'type' => 'attribute',
                            'dir' => \dirname($reflection->getFileName()),
                            'prefix' => $reflection->getNamespaceName(),
                        ],
                    ],
                ],
            ]
        );

        $container->prependExtensionConfig(
            'framework',
            [
                'workflows' => [
                    'entity_migration' => [
                        'type' => 'state_machine',
                        'marking_store' => [
                            'type' => 'method',
                            'property' => 'state',
                        ],
                        'supports' => [
                            EntityMigrationInterface::class,
                        ],
                        'initial_marking' => 'new',
                        'places' => [
                            'new',
                            'queued',
                            'processing',
                            'errored',
                            'completed',
                            'paused',
                            'skipped',
                        ],
                        'transitions' => [
                            'queue' => [
                                'from' => [
                                    'new',
                                ],
                                'to' => 'queued',
                            ],
                            'pause' => [
                                'from' => [
                                    'new',
                                    'queued',
                                ],
                                'to' => 'paused',
                            ],
                            'skip' => [
                                'from' => [
                                    'new',
                                    'queued',
                                ],
                                'to' => 'skipped',
                            ],
                            'process' => [
                                'from' => [
                                    'new',
                                    'queued',
                                ],
                                'to' => 'processing',
                            ],
                            'error' => [
                                'from' => [
                                    'processing',
                                ],
                                'to' => 'errored',
                            ],
                            'complete' => [
                                'from' => [
                                    'processing',
                                ],
                                'to' => 'completed',
                            ],
                            'retry' => [
                                'from' => [
                                    'errored',
                                ],
                                'to' => 'queued',
                            ],
                            'reprocess' => [
                                'from' => [
                                    'completed',
                                ],
                                'to' => 'queued',
                            ],
                            're_queue' => [
                                'from' => [
                                    'paused',
                                    'processing',
                                    'queued',
                                ],
                                'to' => 'queued',
                            ],
                        ],
                    ],
                ],
            ],
        );
    }
}
