<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Draw\Component\Application\Command\UpdateDeployedVersionCommand;
use Draw\Component\Application\Listener\FetchRunningVersionListener;
use Draw\Component\Application\VersionManager;
use Draw\Contracts\Application\VersionVerificationInterface;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        ->defaults()
            ->autoconfigure()
            ->autowire()

        ->set('draw.versioning.command.update_deployed_version', UpdateDeployedVersionCommand::class)
        ->alias(UpdateDeployedVersionCommand::class, 'draw.versioning.command.update_deployed_version')

        ->set('draw.versioning.fetch_running_version_listener', FetchRunningVersionListener::class)
            ->arg('$projectDirectory', param('kernel.project_dir'))
        ->alias(FetchRunningVersionListener::class, 'draw.versioning.fetch_running_version_listener')

        ->set('draw.versioning.version_manager', VersionManager::class)
        ->alias(VersionManager::class, 'draw.versioning.version_manager')
        ->alias(VersionVerificationInterface::class, 'draw.versioning.version_manager');
};
