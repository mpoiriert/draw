<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    if (\PHP_VERSION_ID < 80400) {
        $container->extension('doctrine', [
            'orm' => [
                'enable_lazy_ghost_objects' => true,
            ],
        ]);
    } else {
        $container->extension('doctrine', [
            'orm' => [
                'enable_native_lazy_objects' => true,
            ],
        ]);
    }
};
