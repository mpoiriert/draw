<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

return static function (ContainerConfigurator $container): void {
    $container
        ->services()
        ->set(HttpClientInterface::class)
        ->factory([HttpClient::class, 'create']);

    $container
        ->extension(
            'aws',
            [
                'version' => 'latest',
                'region' => 'ca-central-1',
            ]
        );

    $container->extension(
        'framework',
        [
            'test' => true,
        ]
    );

    $container->extension(
        'draw_aws_tool_kit',
        [
            'imds_version' => 1,
            'newest_instance_role_check' => [
                'enabled' => true,
            ],
        ]
    );
};
