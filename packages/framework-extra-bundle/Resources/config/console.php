<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Draw\Component\Console\Command\PurgeExecutionCommand;
use Draw\Component\Console\Listener\CommandFlowListener;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        ->defaults()
            ->autoconfigure()
            ->autowire()

        ->set('draw.console.command.purge_execution', PurgeExecutionCommand::class)
        ->alias(PurgeExecutionCommand::class, 'draw.console.command.purge_execution')

        ->set('draw.console.command_flow_listener', CommandFlowListener::class)
        ->alias(CommandFlowListener::class, 'draw.console.command_flow_listener');
};
