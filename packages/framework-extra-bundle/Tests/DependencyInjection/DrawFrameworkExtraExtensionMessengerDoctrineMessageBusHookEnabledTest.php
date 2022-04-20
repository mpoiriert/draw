<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Messenger\EnvelopeFactory\EnvelopeFactoryInterface;
use Draw\Component\Messenger\EventListener\DoctrineBusMessageListener;

class DrawFrameworkExtraExtensionMessengerDoctrineMessageBusHookEnabledTest extends DrawFrameworkExtraExtensionMessengerTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['messenger']['doctrine_message_bus_hook'] = [
            'enabled' => true,
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
}
