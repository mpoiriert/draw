<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Draw\Component\Console\Listener\CommandFlowListener;
use Draw\Component\OpenApi\Extraction\Extractor\Constraint\BaseConstraintExtractor;

return static function (ContainerConfigurator $container) {
    $services = $container
        ->services()

        ->defaults()
            ->autoconfigure()
            ->autowire()

        ->load()

        ->set('draw.open_api.constraint_extractor', BaseConstraintExtractor::class)
        ->alias(BaseConstraintExtractor::class, 'draw.open_api.constraint_extractor')

        ->set('draw.console.command_flow_listener', CommandFlowListener::class)
        ->alias(CommandFlowListener::class, 'draw.console.command_flow_listener');
};
