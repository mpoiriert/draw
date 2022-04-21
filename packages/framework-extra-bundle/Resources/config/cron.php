<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Draw\Component\Application\Command\CronDumpToFileCommand;
use Draw\Component\Application\CronManager;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        ->defaults()
            ->autoconfigure()
            ->autowire()

        ->set('draw.cron.command.dump_to_file', CronDumpToFileCommand::class)
        ->alias(CronDumpToFileCommand::class, 'draw.cron.command.dump_to_file')

        ->set('draw.cron.manager', CronManager::class)
        ->alias(CronManager::class, 'draw.cron.manager');
};
