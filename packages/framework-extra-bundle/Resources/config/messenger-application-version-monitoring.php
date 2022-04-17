<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Draw\Component\Messenger\EventListener\StopOnNewVersionListener;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        ->defaults()
        ->autoconfigure()
        ->autowire()

        ->set('draw.messenger.stop_on_new_version_listener', StopOnNewVersionListener::class)
        ->alias(StopOnNewVersionListener::class, 'draw.messenger.stop_on_new_version_listener');
};
