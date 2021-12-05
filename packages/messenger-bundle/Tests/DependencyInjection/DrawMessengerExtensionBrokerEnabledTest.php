<?php

namespace Draw\Bundle\MessengerBundle\Tests\DependencyInjection;

use Draw\Bundle\MessengerBundle\Broker\Broker;
use Draw\Bundle\MessengerBundle\Broker\Command\StartMessageBrokerCommand;
use Draw\Bundle\MessengerBundle\Broker\EventListener\DefaultValuesListener;
use Draw\Bundle\MessengerBundle\Broker\EventListener\PcntSignalListener;
use Draw\Bundle\MessengerBundle\DependencyInjection\DrawMessengerExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawMessengerExtensionBrokerEnabledTest extends DrawMessengerExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawMessengerExtension();
    }

    public function getConfiguration(): array
    {
        return [
            'broker' => [
                'receivers' => ['async'],
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [Broker::class];
        yield [StartMessageBrokerCommand::class];
        yield [DefaultValuesListener::class];
        yield [PcntSignalListener::class];
    }
}
