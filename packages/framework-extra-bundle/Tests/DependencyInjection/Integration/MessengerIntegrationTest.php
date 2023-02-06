<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\DoctrineExtension;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\MessengerIntegration;
use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension;
use Draw\Component\Messenger\AutoStamp\EventListener\AutoStampEnvelopeListener;
use Draw\Component\Messenger\Broker\Broker;
use Draw\Component\Messenger\Broker\Command\StartMessengerBrokerCommand;
use Draw\Component\Messenger\Broker\EventListener\BrokerDefaultValuesListener;
use Draw\Component\Messenger\Broker\EventListener\StopBrokerOnSigtermSignalListener;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\EventListener\PropertyReferenceEncodingListener;
use Draw\Component\Messenger\DoctrineMessageBusHook\EnvelopeFactory\BasicEnvelopeFactory;
use Draw\Component\Messenger\DoctrineMessageBusHook\EnvelopeFactory\EnvelopeFactoryInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\EventListener\DoctrineBusMessageListener;
use Draw\Component\Messenger\DoctrineMessageBusHook\EventListener\EnvelopeFactoryDelayStampListener;
use Draw\Component\Messenger\DoctrineMessageBusHook\EventListener\EnvelopeFactoryDispatchAfterCurrentBusStampListener;
use Draw\Component\Messenger\Expirable\Command\PurgeExpiredMessageCommand;
use Draw\Component\Messenger\ManualTrigger\Action\ClickMessageAction;
use Draw\Component\Messenger\ManualTrigger\EventListener\StampManuallyTriggeredEnvelopeListener;
use Draw\Component\Messenger\ManualTrigger\ManuallyTriggeredMessageUrlGenerator;
use Draw\Component\Messenger\ManualTrigger\MessageHandler\RedirectToRouteMessageHandler;
use Draw\Component\Messenger\Message\AsyncHighPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncLowPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncMessageInterface;
use Draw\Component\Messenger\Searchable\EnvelopeFinder;
use Draw\Component\Messenger\Searchable\TransportRepository;
use Draw\Component\Messenger\SerializerEventDispatcher\EventDispatcherSerializerDecorator;
use Draw\Component\Messenger\Transport\DrawTransportFactory;
use Draw\Component\Messenger\Transport\Entity\DrawMessageInterface;
use Draw\Component\Messenger\Transport\Entity\DrawMessageTagInterface;
use Draw\Component\Messenger\Versioning\EventListener\StopOnNewVersionListener;
use Draw\Contracts\Messenger\EnvelopeFinderInterface;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\MessengerIntegration
 *
 * @property MessengerIntegration $integration
 */
class MessengerIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new MessengerIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'messenger';
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'entity_class' => 'App\Entity\MessengerMessage',
            'tag_entity_class' => 'App\Entity\MessengerMessageTag',
            'async_routing_configuration' => [
                'enabled' => false,
            ],
            'broker' => [
                'enabled' => false,
                'contexts' => [],
            ],
            'doctrine_message_bus_hook' => [
                'enabled' => false,
                'envelope_factory' => [
                    'dispatch_after_current_bus' => [
                        'enabled' => true,
                    ],
                    'delay' => [
                        'enabled' => false,
                        'delay_in_milliseconds' => 2500,
                    ],
                ],
            ],
            'serializer_event_dispatcher' => [
                'enabled' => false,
                'transport' => 'messenger.transport.native_php_serializer',
            ],
            'versioning' => [
                'enabled' => false,
                'stop_on_new_version' => [
                    'enabled' => true,
                ],
            ],
        ];
    }

    public function provideTestLoad(): iterable
    {
        $defaultServices = [
            new ServiceConfiguration(
                'draw.messenger.doctrine_envelope_entity_reference.event_listener.property_reference_encoding_listener',
                [
                    PropertyReferenceEncodingListener::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.messenger.transport.draw_transport_factory',
                [
                    DrawTransportFactory::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.messenger.manual_trigger.message_handler.redirect_to_route_message_handler',
                [
                    RedirectToRouteMessageHandler::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.messenger.manual_trigger.event_listener.stamp_manually_triggered_envelope_listener',
                [
                    StampManuallyTriggeredEnvelopeListener::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.messenger.expirable.command.purge_expired_message_command',
                [
                    PurgeExpiredMessageCommand::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.messenger.auto_stamp.event_listener.auto_stamp_envelope_listener',
                [
                    AutoStampEnvelopeListener::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.messenger.manual_trigger.manually_triggered_message_url_generator',
                [
                    ManuallyTriggeredMessageUrlGenerator::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.messenger.searchable.envelope_finder',
                [
                    EnvelopeFinder::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.messenger.searchable.transport_repository',
                [
                    TransportRepository::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.messenger.manual_trigger.action.click_message_action',
                [ClickMessageAction::class]
            ),
        ];

        $defaultAliases = [
            EnvelopeFinder::class => [
                EnvelopeFinderInterface::class,
            ],
        ];

        yield 'default' => [
            [],
            $defaultServices,
            $defaultAliases,
        ];

        yield 'async_routing_configuration' => [
            [
                [
                    'async_routing_configuration' => true,
                ],
            ],
            array_merge(
                $defaultServices,
                []
            ),
            $defaultAliases,
        ];

        yield 'serializer_event_dispatcher' => [
            [
                [
                    'serializer_event_dispatcher' => [
                        'transport' => 'messenger.transport.serializer',
                    ],
                ],
            ],
            array_merge(
                $defaultServices,
                [
                    new ServiceConfiguration(
                        'draw.messenger.serializer_event_dispatcher.event_dispatcher_serializer_decorator',
                        [
                            EventDispatcherSerializerDecorator::class,
                        ],
                        function (Definition $definition): void {
                            static::assertSame(
                                ['messenger.transport.serializer', 'messenger.transport.serializer.inner', 0],
                                $definition->getDecoratedService(),
                            );

                            $argument = $definition->getArgument(0);

                            static::assertInstanceOf(
                                Reference::class,
                                $argument
                            );

                            static::assertSame(
                                'messenger.transport.serializer.inner',
                                (string) $argument
                            );
                        }
                    ),
                ]
            ),
            $defaultAliases,
        ];

        yield 'versioning' => [
            [
                [
                    'versioning' => true,
                ],
            ],
            array_merge(
                $defaultServices,
                [
                    new ServiceConfiguration(
                        'draw.messenger.versioning.event_listener.stop_on_new_version_listener',
                        [
                            StopOnNewVersionListener::class,
                        ]
                    ),
                ]
            ),
            $defaultAliases,
        ];

        yield 'broker' => [
            [
                [
                    'broker' => [
                        'contexts' => [
                            'default' => [
                                'receivers' => ['async'],
                            ],
                        ],
                    ],
                ],
            ],
            array_merge(
                $defaultServices,
                [
                    new ServiceConfiguration(
                        'draw.messenger.broker.command.start_messenger_broker_command',
                        [
                            StartMessengerBrokerCommand::class,
                        ]
                    ),
                    new ServiceConfiguration(
                        'draw.messenger.broker.event_listener.broker_default_values_listener',
                        [
                            BrokerDefaultValuesListener::class,
                        ],
                        function (Definition $definition): void {
                            static::assertSame(
                                [
                                    'default' => [
                                        'receivers' => ['async'],
                                        'defaultOptions' => [],
                                    ],
                                ],
                                $definition->getArgument('$contexts')
                            );
                        }
                    ),
                    new ServiceConfiguration(
                        'draw.messenger.broker.event_listener.stop_broker_on_sigterm_signal_listener',
                        [
                            StopBrokerOnSigtermSignalListener::class,
                        ]
                    ),
                ]
            ),
            $defaultAliases,
        ];

        yield 'doctrine_message_bus_hook' => [
            [
                [
                    'doctrine_message_bus_hook' => [
                        'envelope_factory' => [
                            'delay' => [
                                'enabled' => true,
                                'delay_in_milliseconds' => 5000,
                            ],
                        ],
                    ],
                ],
            ],
            array_merge(
                $defaultServices,
                [
                    new ServiceConfiguration(
                        'draw.messenger.doctrine_message_bus_hook.event_listener.doctrine_bus_message_listener',
                        [
                            DoctrineBusMessageListener::class,
                        ],
                        function (Definition $definition): void {
                            static::assertSame(
                                [
                                    'doctrine.event_subscriber' => [
                                        [],
                                    ],
                                ],
                                $definition->getTags()
                            );
                        }
                    ),
                    new ServiceConfiguration(
                        'draw.messenger.doctrine_message_bus_hook.envelope_factory.basic_envelope_factory',
                        [
                            BasicEnvelopeFactory::class,
                        ]
                    ),
                    new ServiceConfiguration(
                        'draw.messenger.doctrine_message_bus_hook.event_listener.envelope_factory_dispatch_after_current_bus_stamp_listener',
                        [
                            EnvelopeFactoryDispatchAfterCurrentBusStampListener::class,
                        ]
                    ),
                    new ServiceConfiguration(
                        'draw.messenger.doctrine_message_bus_hook.event_listener.envelope_factory_delay_stamp_listener',
                        [
                            EnvelopeFactoryDelayStampListener::class,
                        ],
                        function (Definition $definition): void {
                            static::assertSame(
                                [
                                    '$delay' => 5000,
                                ],
                                $definition->getArguments()
                            );
                        }
                    ),
                ]
            ),
            [
                ...$defaultAliases,
                ...[
                    BasicEnvelopeFactory::class => [
                        EnvelopeFactoryInterface::class,
                    ],
                ],
            ],
        ];
    }

    public function testPrepend(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->registerExtension(new DoctrineExtension());
        $containerBuilder->registerExtension(new FrameworkExtension());
        $containerBuilder->registerExtension(new DrawSonataIntegrationExtension());

        $this->integration->prepend(
            $containerBuilder,
            $this->processConfiguration([
                $this->getDefaultConfiguration(),
                [
                    'entity_class' => MessengerMessageStub::class,
                    'tag_entity_class' => MessengerMessageTagStub::class,
                    'broker' => [
                        'contexts' => [
                            'default' => ['receivers' => ['async']],
                        ],
                    ],
                    'async_routing_configuration' => true,
                ],
            ]),
        );

        $installationPath = \dirname((new \ReflectionClass(Broker::class))->getFileName(), 2);

        static::assertContainerExtensionConfiguration(
            $containerBuilder,
            [
                'doctrine' => [
                    [
                        'orm' => [
                            'resolve_target_entities' => [
                                DrawMessageInterface::class => MessengerMessageStub::class,
                                DrawMessageTagInterface::class => MessengerMessageTagStub::class,
                            ],
                        ],
                    ],
                ],
                'framework' => [
                    [
                        'messenger' => [
                            'routing' => [
                                AsyncMessageInterface::class => 'async',
                                AsyncHighPriorityMessageInterface::class => 'async_high_priority',
                                AsyncLowPriorityMessageInterface::class => 'async_low_priority',
                            ],
                        ],
                    ],
                    [
                        'translator' => [
                            'paths' => [
                                'draw-messenger' => $installationPath.'/Resources/translations',
                            ],
                        ],
                    ],
                ],
                'draw_sonata_integration' => [
                    [
                        'messenger' => [
                            'admin' => [
                                'entity_class' => MessengerMessageStub::class,
                            ],
                        ],
                    ],
                ],
            ],
        );
    }
}

class MessengerMessageStub
{
}

class MessengerMessageTagStub
{
}
