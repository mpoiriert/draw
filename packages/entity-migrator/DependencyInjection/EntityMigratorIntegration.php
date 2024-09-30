<?php

namespace Draw\Component\EntityMigrator\DependencyInjection;

use Draw\Component\DependencyInjection\Integration\ContainerBuilderIntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationTrait;
use Draw\Component\DependencyInjection\Integration\PrependIntegrationInterface;
use Draw\Component\EntityMigrator\DependencyInjection\Compiler\EntityMigratorCompilerPass;
use Draw\Component\EntityMigrator\Entity\EntityMigrationInterface;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\Message\MigrateEntityCommand;
use Draw\Component\EntityMigrator\MigrationInterface;
use Draw\Component\EntityMigrator\Migrator;
use Draw\Component\EntityMigrator\Workflow\EntityMigrationWorkflow;
use Draw\Component\EntityMigrator\Workflow\MigrationWorkflow;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class EntityMigratorIntegration implements IntegrationInterface, ContainerBuilderIntegrationInterface, PrependIntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'entity_migrator';
    }

    public function buildContainer(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new EntityMigratorCompilerPass());
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(MigrationInterface::class)
            ->addTag(MigrationInterface::class)
        ;

        $this->registerClasses(
            $loader,
            $namespace = 'Draw\Component\EntityMigrator\\',
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
            ->end()
        ;
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
                    MigrationWorkflow::NAME => [
                        'type' => 'state_machine',
                        'marking_store' => [
                            'type' => 'method',
                            'property' => 'state',
                        ],
                        'supports' => [
                            Migration::class,
                        ],
                        'initial_marking' => MigrationWorkflow::PLACE_NEW,
                        'places' => MigrationWorkflow::places(),
                        'transitions' => [
                            MigrationWorkflow::TRANSITION_PROCESS => [
                                'from' => [
                                    MigrationWorkflow::PLACE_NEW,
                                ],
                                'to' => MigrationWorkflow::PLACE_PROCESSING,
                            ],
                            MigrationWorkflow::TRANSITION_PAUSE => [
                                'from' => [
                                    MigrationWorkflow::PLACE_NEW,
                                    MigrationWorkflow::PLACE_PROCESSING,
                                ],
                                'to' => MigrationWorkflow::PLACE_PAUSED,
                            ],
                            MigrationWorkflow::TRANSITION_COMPLETE => [
                                'from' => [
                                    MigrationWorkflow::PLACE_PROCESSING,
                                    MigrationWorkflow::PLACE_ERROR,
                                ],
                                'to' => MigrationWorkflow::PLACE_COMPLETED,
                            ],
                            MigrationWorkflow::TRANSITION_ERROR => [
                                'from' => [
                                    MigrationWorkflow::PLACE_PROCESSING,
                                ],
                                'to' => MigrationWorkflow::PLACE_ERROR,
                            ],
                        ],
                    ],
                    EntityMigrationWorkflow::NAME => [
                        'type' => 'state_machine',
                        'marking_store' => [
                            'type' => 'method',
                            'property' => 'state',
                        ],
                        'supports' => [
                            EntityMigrationInterface::class,
                        ],
                        'initial_marking' => EntityMigrationWorkflow::PLACE_NEW,
                        'places' => EntityMigrationWorkflow::places(),
                        'transitions' => [
                            EntityMigrationWorkflow::TRANSITION_QUEUE => [
                                'from' => [
                                    EntityMigrationWorkflow::PLACE_NEW,
                                ],
                                'to' => EntityMigrationWorkflow::PLACE_QUEUED,
                            ],
                            EntityMigrationWorkflow::TRANSITION_PAUSE => [
                                'from' => [
                                    EntityMigrationWorkflow::PLACE_NEW,
                                    EntityMigrationWorkflow::PLACE_QUEUED,
                                ],
                                'to' => EntityMigrationWorkflow::PLACE_PAUSED,
                            ],
                            EntityMigrationWorkflow::TRANSITION_SKIP => [
                                'from' => [
                                    EntityMigrationWorkflow::PLACE_NEW,
                                    EntityMigrationWorkflow::PLACE_QUEUED,
                                ],
                                'to' => EntityMigrationWorkflow::PLACE_SKIPPED,
                            ],
                            EntityMigrationWorkflow::TRANSITION_PROCESS => [
                                'from' => [
                                    EntityMigrationWorkflow::PLACE_NEW,
                                    EntityMigrationWorkflow::PLACE_QUEUED,
                                ],
                                'to' => EntityMigrationWorkflow::PLACE_PROCESSING,
                            ],
                            EntityMigrationWorkflow::TRANSITION_FAIL => [
                                'from' => [
                                    EntityMigrationWorkflow::PLACE_PROCESSING,
                                ],
                                'to' => EntityMigrationWorkflow::PLACE_FAILED,
                            ],
                            EntityMigrationWorkflow::TRANSITION_COMPLETE => [
                                'from' => [
                                    EntityMigrationWorkflow::PLACE_PROCESSING,
                                ],
                                'to' => EntityMigrationWorkflow::PLACE_COMPLETED,
                            ],
                            EntityMigrationWorkflow::TRANSITION_RETRY => [
                                'from' => [
                                    EntityMigrationWorkflow::PLACE_FAILED,
                                ],
                                'to' => EntityMigrationWorkflow::PLACE_QUEUED,
                            ],
                            EntityMigrationWorkflow::TRANSITION_REPROCESS => [
                                'from' => [
                                    EntityMigrationWorkflow::PLACE_COMPLETED,
                                    EntityMigrationWorkflow::PLACE_SKIPPED,
                                ],
                                'to' => EntityMigrationWorkflow::PLACE_QUEUED,
                            ],
                            EntityMigrationWorkflow::TRANSITION_REQUEUE => [
                                'from' => [
                                    EntityMigrationWorkflow::PLACE_PAUSED,
                                    EntityMigrationWorkflow::PLACE_PROCESSING,
                                    EntityMigrationWorkflow::PLACE_QUEUED,
                                ],
                                'to' => EntityMigrationWorkflow::PLACE_QUEUED,
                            ],
                        ],
                    ],
                ],
            ],
        );
    }
}
