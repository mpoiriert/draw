<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Draw\Component\Messenger\Command\StartMessengerBrokerCommand;
use Draw\Component\Messenger\EventListener\BrokerDefaultValuesListener;
use Draw\Component\Messenger\EventListener\StopBrokerOnSigtermSignalListener;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        ->defaults()
        ->autoconfigure()
        ->autowire()

        ->set('draw.messenger.command.start_messenger_broker', StartMessengerBrokerCommand::class)
        ->alias(StartMessengerBrokerCommand::class, 'draw.messenger.command.start_messenger_broker')

        ->set('draw.messenger.broker_default_values_listener', BrokerDefaultValuesListener::class)
        ->alias(BrokerDefaultValuesListener::class, 'draw.messenger.broker_default_values_listener')

        ->set('draw.messenger.stop_broker_on_sigterm_signal_listener', StopBrokerOnSigtermSignalListener::class)
        ->alias(StopBrokerOnSigtermSignalListener::class, 'draw.messenger.stop_broker_on_sigterm_signal_listener');
};
