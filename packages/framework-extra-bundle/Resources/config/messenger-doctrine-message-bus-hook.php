<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Draw\Component\Messenger\EnvelopeFactory\BasicEnvelopeFactory;
use Draw\Component\Messenger\EnvelopeFactory\EnvelopeFactoryInterface;
use Draw\Component\Messenger\EventListener\DoctrineBusMessageListener;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        ->defaults()
        ->autoconfigure()
        ->autowire()

        ->set('draw.messenger.basic_envelope_factory', BasicEnvelopeFactory::class)
        ->alias(EnvelopeFactoryInterface::class, 'draw.messenger.basic_envelope_factory')

        ->set('draw.messenger.doctrine_bus_message_listener', DoctrineBusMessageListener::class)
        ->tag('doctrine.event_subscriber')
        ->alias(DoctrineBusMessageListener::class, 'draw.messenger.doctrine_bus_message_listener');
};
