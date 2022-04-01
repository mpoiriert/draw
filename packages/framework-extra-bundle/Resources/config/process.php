<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Draw\Component\Process\ProcessFactory;
use Draw\Contracts\Process\ProcessFactoryInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('draw.process.factory', ProcessFactory::class)
        ->alias(ProcessFactoryInterface::class, 'draw.process.factory');
};
