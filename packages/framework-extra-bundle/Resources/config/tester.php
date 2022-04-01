<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Draw\Component\Tester\Command\TestsCoverageCheckCommand;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('draw.tester.command.coverage_check', TestsCoverageCheckCommand::class)
        ->autoconfigure()
        ->autowire()
        ->alias(TestsCoverageCheckCommand::class, 'draw.tester.command.coverage_check');
};
