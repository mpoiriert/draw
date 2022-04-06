<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Draw\Component\Tester\Command\TestsCoverageCheckCommand;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        ->defaults()
        ->autoconfigure()
        ->autowire()

        ->set('draw.tester.command.coverage_check', TestsCoverageCheckCommand::class)
        ->alias(TestsCoverageCheckCommand::class, 'draw.tester.command.coverage_check');
};
