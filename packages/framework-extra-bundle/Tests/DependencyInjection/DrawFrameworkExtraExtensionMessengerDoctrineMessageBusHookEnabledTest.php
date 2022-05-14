<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Messenger\EnvelopeFactory\EnvelopeFactoryInterface;
use Draw\Component\Messenger\EventListener\DoctrineBusMessageListener;
use Draw\Component\Messenger\EventListener\EnvelopeFactoryDelayStampListener;
use Draw\Component\Messenger\EventListener\EnvelopeFactoryDispatchAfterCurrentBusStampListener;

class DrawFrameworkExtraExtensionMessengerDoctrineMessageBusHookEnabledTest extends DrawFrameworkExtraExtensionMessengerTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['messenger']['doctrine_message_bus_hook'] = [
            'enabled' => true,
            'envelope_factory' => [
                'delay' => [
                    'enabled' => true,
                    'delay_in_milliseconds' => 5000,
                ],
            ],
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.messenger.doctrine_bus_message_listener'];
        yield [DoctrineBusMessageListener::class, 'draw.messenger.doctrine_bus_message_listener'];
        yield ['draw.messenger.basic_envelope_factory'];
        yield [EnvelopeFactoryInterface::class, 'draw.messenger.basic_envelope_factory'];
        yield ['draw.messenger.event_listener.envelope_factory_dispatch_after_current_bus_stamp_listener'];
        yield [
            EnvelopeFactoryDispatchAfterCurrentBusStampListener::class,
            'draw.messenger.event_listener.envelope_factory_dispatch_after_current_bus_stamp_listener',
        ];
        yield ['draw.messenger.event_listener.envelope_factory_delay_stamp_listener'];
        yield [
            EnvelopeFactoryDelayStampListener::class,
            'draw.messenger.event_listener.envelope_factory_delay_stamp_listener',
        ];
    }

    public function testDoctrineBusMessageListenerDefinition(): void
    {
        $tags = $this->getContainerBuilder()
            ->getDefinition('draw.messenger.doctrine_bus_message_listener')
            ->getTags();

        $this->assertSame(
            [
                'doctrine.event_subscriber' => [
                    [],
                ],
            ],
            $tags
        );
    }

    public function testEnvelopeFactoryDelayStampListenerDefinition(): void
    {
        $this->assertSame(
            [
                '$delay' => 5000,
            ],
            $this->getContainerBuilder()
                ->getDefinition('draw.messenger.event_listener.envelope_factory_delay_stamp_listener')
                ->getArguments()
        );
    }
}
