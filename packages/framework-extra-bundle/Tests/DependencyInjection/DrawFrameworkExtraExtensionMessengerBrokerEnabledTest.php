<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Messenger\Command\StartMessengerBrokerCommand;
use Draw\Component\Messenger\EventListener\BrokerDefaultValuesListener;
use Draw\Component\Messenger\EventListener\StopBrokerOnSigtermSignalListener;

class DrawFrameworkExtraExtensionMessengerBrokerEnabledTest extends DrawFrameworkExtraExtensionMessengerTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['messenger']['broker'] = [
            'receivers' => ['async'],
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.messenger.command.start_messenger_broker'];
        yield [StartMessengerBrokerCommand::class, 'draw.messenger.command.start_messenger_broker'];
        yield ['draw.messenger.broker_default_values_listener'];
        yield [BrokerDefaultValuesListener::class, 'draw.messenger.broker_default_values_listener'];
        yield ['draw.messenger.stop_broker_on_sigterm_signal_listener'];
        yield [StopBrokerOnSigtermSignalListener::class, 'draw.messenger.stop_broker_on_sigterm_signal_listener'];
    }
}
