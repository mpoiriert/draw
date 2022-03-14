<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension(
        'framework',
        [
            'test' => true,
        ]
    );

    $container->extension(
        'draw_application',
        [
            'configuration' => [
                'enabled' => true,
            ],
            'versioning' => [
                'enabled' => true,
            ],
        ]
    );

    $container->extension(
        'doctrine',
        [
            'dbal' => [
                'driver' => 'mysql',
                'server_version' => '5.7',
                'charset' => 'utf8mb4',
                'default_table_options' => [
                    'charset' => 'utf8mb4',
                    'collate' => 'utf8mb4_unicode_ci',
                ],
                'url' => '%env(resolve:DATABASE_URL)%',
            ],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                'auto_mapping' => true,
                'mappings' => [
                    'Application' => [
                        'is_bundle' => false,
                        'type' => 'annotation',
                        'dir' => __DIR__.'/../Configuration/Entity',
                        'prefix' => 'Draw\Bundle\ApplicationBundle\Configuration\Entity',
                    ],
                ],
            ],
        ]
    );
};
