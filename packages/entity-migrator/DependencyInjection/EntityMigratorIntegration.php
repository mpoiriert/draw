<?php

namespace Draw\Component\EntityMigrator\DependencyInjection;

use Draw\Component\DependencyInjection\Integration\ContainerBuilderIntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationTrait;
use Draw\Component\DependencyInjection\Integration\PrependIntegrationInterface;
use Draw\Component\EntityMigrator\Command\MigrateCommand;
use Draw\Component\EntityMigrator\Command\QueueBatchCommand;
use Draw\Component\EntityMigrator\DependencyInjection\Compiler\EntityMigratorCompilerPass;
use Draw\Component\EntityMigrator\Entity\BaseEntityMigration;
use Draw\Component\EntityMigrator\Entity\EntityMigrationInterface;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\Message\MigrateEntityCommand;
use Draw\Component\EntityMigrator\MigrationInterface;
use Draw\Component\EntityMigrator\Migrator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

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
            ->addTag(MigrationInterface::class);

        $this->registerClasses(
            $loader,
            $namespace = 'Draw\\Component\\EntityMigrator\\',
            \dirname((new \ReflectionClass(Migrator::class))->getFileName()),
        );

        $container
            ->getDefinition(MigrateCommand::class)
            ->setArgument('$servicesResetter', new Reference('services_resetter'));

        $container
            ->getDefinition(QueueBatchCommand::class)
            ->setArgument('$servicesResetter', new Reference('services_resetter'));

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
                        'initial_marking' => BaseEntityMigration::STATE_NEW,
                        'places' => [
                            BaseEntityMigration::STATE_NEW,
                            BaseEntityMigration::STATE_QUEUED,
                            BaseEntityMigration::STATE_PROCESSING,
                            BaseEntityMigration::STATE_FAILED,
                            BaseEntityMigration::STATE_COMPLETED,
                            BaseEntityMigration::STATE_PAUSED,
                            BaseEntityMigration::STATE_SKIPPED,
                        ],
                        'transitions' => [
                            'queue' => [
                                'from' => [
                                    BaseEntityMigration::STATE_NEW,
                                ],
                                'to' => BaseEntityMigration::STATE_QUEUED,
                            ],
                            'pause' => [
                                'from' => [
                                    BaseEntityMigration::STATE_NEW,
                                    BaseEntityMigration::STATE_QUEUED,
                                ],
                                'to' => BaseEntityMigration::STATE_PAUSED,
                            ],
                            'skip' => [
                                'from' => [
                                    BaseEntityMigration::STATE_NEW,
                                    BaseEntityMigration::STATE_QUEUED,
                                ],
                                'to' => BaseEntityMigration::STATE_SKIPPED,
                            ],
                            'process' => [
                                'from' => [
                                    BaseEntityMigration::STATE_NEW,
                                    BaseEntityMigration::STATE_QUEUED,
                                ],
                                'to' => BaseEntityMigration::STATE_PROCESSING,
                            ],
                            'fail' => [
                                'from' => [
                                    BaseEntityMigration::STATE_PROCESSING,
                                ],
                                'to' => 'failed',
                            ],
                            'complete' => [
                                'from' => [
                                    BaseEntityMigration::STATE_PROCESSING,
                                ],
                                'to' => BaseEntityMigration::STATE_COMPLETED,
                            ],
                            'retry' => [
                                'from' => [
                                    'failed',
                                ],
                                'to' => BaseEntityMigration::STATE_QUEUED,
                            ],
                            'reprocess' => [
                                'from' => [
                                    BaseEntityMigration::STATE_COMPLETED,
                                ],
                                'to' => BaseEntityMigration::STATE_QUEUED,
                            ],
                            're_queue' => [
                                'from' => [
                                    BaseEntityMigration::STATE_PAUSED,
                                    BaseEntityMigration::STATE_PROCESSING,
                                    BaseEntityMigration::STATE_QUEUED,
                                ],
                                'to' => BaseEntityMigration::STATE_QUEUED,
                            ],
                        ],
                    ],
                ],
            ],
        );
    }
}
