<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use App\Entity\MessengerMessage;
use App\Entity\MessengerMessageTag;
use Draw\Component\Messenger\Broker\Broker;
use Draw\Component\Messenger\Broker\EventListener\BrokerDefaultValuesListener;
use Draw\Component\Messenger\DoctrineMessageBusHook\EnvelopeFactory\BasicEnvelopeFactory;
use Draw\Component\Messenger\DoctrineMessageBusHook\EnvelopeFactory\EnvelopeFactoryInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\EventListener\DoctrineBusMessageListener;
use Draw\Component\Messenger\DoctrineMessageBusHook\EventListener\EnvelopeFactoryDelayStampListener;
use Draw\Component\Messenger\DoctrineMessageBusHook\EventListener\EnvelopeFactoryDispatchAfterCurrentBusStampListener;
use Draw\Component\Messenger\Message\AsyncHighPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncLowPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncMessageInterface;
use Draw\Component\Messenger\Retry\EventDrivenRetryStrategy;
use Draw\Component\Messenger\Searchable\EnvelopeFinder;
use Draw\Component\Messenger\Searchable\TransportRepository;
use Draw\Component\Messenger\SerializerEventDispatcher\EventDispatcherSerializerDecorator;
use Draw\Component\Messenger\SerializerEventDispatcher\PhpEventDispatcherSerializerDecorator;
use Draw\Component\Messenger\Transport\DrawTransportFactory;
use Draw\Component\Messenger\Transport\Entity\DrawMessageInterface;
use Draw\Component\Messenger\Transport\Entity\DrawMessageTagInterface;
use Draw\Component\Messenger\Versioning\EventListener\StopOnNewVersionListener;
use Draw\Contracts\Messenger\EnvelopeFinderInterface;
use Draw\Contracts\Messenger\TransportRepositoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

class MessengerIntegration implements IntegrationInterface, PrependIntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'messenger';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $namespace = 'Draw\\Component\\Messenger\\';
        $rootDirectory = \dirname((new \ReflectionClass(Broker::class))->getFileName(), 2);

        $directories = glob($rootDirectory.'/*', \GLOB_ONLYDIR);

        $ignoreFolders = [
            'Broker',
            'DoctrineMessageBusHook',
            'Message',
            'Resources',
            'SerializerEventDispatcher',
            'Tests',
            'Versioning',
        ];

        foreach ($directories as $directory) {
            $dirname = basename($directory);
            if (\in_array($dirname, $ignoreFolders)) {
                continue;
            }

            $exclude = [];

            if ('Searchable' === $dirname) {
                $exclude[] = $directory.'/Filter/';
            }

            if ('Transport' === $dirname) {
                $exclude[] = $directory.'/DrawTransport.php';
            }

            $this->registerClasses(
                $loader,
                sprintf('%s%s\\', $namespace, $dirname),
                $directory,
                $exclude
            );
        }

        $this->loadBroker($config['broker'], $loader, $container);
        $this->loadDoctrineMessageBusHook($config['doctrine_message_bus_hook'], $loader, $container);
        $this->loadRetry($config['retry'], $loader, $container);
        $this->loadSerializerEventDispatcher($config['serializer_event_dispatcher'], $loader, $container);
        $this->loadVersioning($config['versioning'], $loader, $container);

        $container
            ->getDefinition(DrawTransportFactory::class)
            ->setArgument('$registry', new Reference('doctrine'));

        $container
            ->getDefinition(TransportRepository::class)
            ->setBindings(
                [
                    ContainerInterface::class.' $transportLocator' => new Reference('messenger.receiver_locator'),
                    '$transportNames' => new Parameter('draw.messenger.transport_names'),
                ]
            );

        $container
            ->setAlias(TransportRepositoryInterface::class, TransportRepository::class);

        $container
            ->setAlias(EnvelopeFinderInterface::class, EnvelopeFinder::class);

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.messenger.'
        );

        $container
            ->getDefinition('draw.messenger.manual_trigger.action.click_message_action')
            ->addTag('controller.service_arguments');
    }

    private function loadBroker(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $this->registerClasses(
            $loader,
            'Draw\\Component\\Messenger\\Broker\\',
            $directory = \dirname((new \ReflectionClass(Broker::class))->getFileName()),
            [
                $directory.'/Broker.php',
            ]
        );

        $contexts = [];

        foreach ($config['contexts'] as $name => $contextConfiguration) {
            $defaultOptions = [];

            foreach ($contextConfiguration['default_options'] as $option) {
                $defaultOptions[$option['name']] = $option['value'];
            }

            $contexts[$name] = [
                'receivers' => $contextConfiguration['receivers'],
                'defaultOptions' => $defaultOptions,
            ];
        }

        $container
            ->getDefinition(BrokerDefaultValuesListener::class)
            ->setArgument('$contexts', $contexts);
    }

    private function loadDoctrineMessageBusHook(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $this->registerClasses(
            $loader,
            'Draw\\Component\\Messenger\\DoctrineMessageBusHook\\',
            \dirname((new \ReflectionClass(EnvelopeFactoryInterface::class))->getFileName(), 2),
        );

        $container->getDefinition(DoctrineBusMessageListener::class)
            ->addTag('doctrine.event_listener', ['event' => 'postPersist'])
            ->addTag('doctrine.event_listener', ['event' => 'postLoad'])
            ->addTag('doctrine.event_listener', ['event' => 'postFlush'])
            ->addTag('doctrine.event_listener', ['event' => 'onClear'])
            ->addTag('doctrine_mongodb.odm.event_listener', ['event' => 'postPersist'])
            ->addTag('doctrine_mongodb.odm.event_listener', ['event' => 'postLoad'])
            ->addTag('doctrine_mongodb.odm.event_listener', ['event' => 'postFlush'])
            ->addTag('doctrine_mongodb.odm.event_listener', ['event' => 'onClear']);

        $container
            ->setAlias(EnvelopeFactoryInterface::class, BasicEnvelopeFactory::class);

        $envelopeFactoryConfig = $config['envelope_factory'];
        if (!$this->isConfigEnabled($container, $envelopeFactoryConfig['dispatch_after_current_bus'])) {
            $container->removeDefinition(EnvelopeFactoryDispatchAfterCurrentBusStampListener::class);
        }

        if (!$this->isConfigEnabled($container, $envelopeFactoryConfig['delay'])) {
            $container->removeDefinition(EnvelopeFactoryDelayStampListener::class);
        } else {
            $container->getDefinition(EnvelopeFactoryDelayStampListener::class)
                ->setArgument('$delay', $envelopeFactoryConfig['delay']['delay_in_milliseconds']);
        }
    }

    private function loadRetry(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        if ($this->isConfigEnabled($container, $config['event_driven'])) {
            foreach ($config['event_driven']['transports'] as $transportName) {
                $retryServiceId = sprintf('messenger.retry.multiplier_retry_strategy.%s', $transportName);
                $decoratorServiceId = 'draw.'.$retryServiceId.'.event_driven.decorated';
                $container->setDefinition(
                    $decoratorServiceId,
                    (new Definition(EventDrivenRetryStrategy::class))
                        ->setDecoratedService($retryServiceId)
                        ->setAutowired(true)
                        ->setArgument('$fallbackRetryStrategy', new Reference($decoratorServiceId.'.inner'))
                );
            }
        }
    }

    private function loadSerializerEventDispatcher(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $this->registerClasses(
            $loader,
            'Draw\\Component\\Messenger\\SerializerEventDispatcher\\',
            \dirname((new \ReflectionClass(EventDispatcherSerializerDecorator::class))->getFileName()),
        );

        foreach ($config['decorate_serializers'] as $key => $serializer) {
            $decoratorClass = EventDispatcherSerializerDecorator::class;
            if ('messenger.transport.native_php_serializer' === $serializer) {
                $decoratorClass = PhpEventDispatcherSerializerDecorator::class;
            }

            $container
                ->setDefinition(
                    'draw.messenger.serializer_event_dispatcher'.$key,
                    (new Definition($decoratorClass))
                        ->setAutowired(true)
                        ->setDecoratedService(
                            $serializer,
                            $serializer.'.inner'
                        )
                        ->setArgument(0, new Reference($serializer.'.inner'))
                );
        }
    }

    private function loadVersioning(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $this->registerClasses(
            $loader,
            'Draw\\Component\\Messenger\\Versioning\\',
            \dirname((new \ReflectionClass(StopOnNewVersionListener::class))->getFileName(), 2),
        );

        if (!$this->isConfigEnabled($container, $config['stop_on_new_version'])) {
            $container->removeDefinition(StopOnNewVersionListener::class);
        }
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('async_routing_configuration')->canBeEnabled()->end()
                ->scalarNode('entity_class')
                    ->validate()
                        ->ifTrue(fn ($value) => !class_exists($value) && MessengerMessage::class !== $value)
                        ->thenInvalid('The class [%s] must exists.')
                    ->end()
                    ->defaultValue(MessengerMessage::class)
                ->end()
                ->scalarNode('tag_entity_class')
                    ->validate()
                        ->ifTrue(fn ($value) => !class_exists($value) && MessengerMessageTag::class !== $value)
                        ->thenInvalid('The class [%s] must exists.')
                    ->end()
                    ->defaultValue(MessengerMessageTag::class)
                ->end()

                ->append($this->createVersioningNode())
                ->append($this->createBrokerNode())
                ->append($this->createDoctrineMessageBusHookNode())
                ->append($this->createRetryNode())
                ->append($this->createSerializerEventDispatcherNode())
            ->end();
    }

    private function createBrokerNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('broker'))
            ->canBeEnabled()
            ->children()
                ->arrayNode('contexts')
                    ->beforeNormalization()
                        ->always(function ($config) {
                            foreach ($config as $name => $configuration) {
                                if (!isset($configuration['name'])) {
                                    $config[$name]['name'] = $name;
                                }
                            }

                            return $config;
                        })
                    ->end()
                    ->validate()
                        ->ifTrue(static fn (array $value): bool => !\array_key_exists('default', $value))
                        ->thenInvalid('You must define a default context.')
                    ->end()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name', false)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->arrayNode('receivers')
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->scalarPrototype()->end()
                            ->end()
                            ->arrayNode('default_options')
                                ->normalizeKeys(false)
                                ->beforeNormalization()
                                ->always(function ($options) {
                                    foreach ($options as $name => $configuration) {
                                        if (!\is_array($configuration)) {
                                            $options[$name] = $configuration = ['name' => $name, 'value' => $configuration];
                                        }
                                        if (\is_int($name)) {
                                            continue;
                                        }
                                        if (!isset($configuration['name'])) {
                                            $options[$name]['name'] = $name;
                                        }
                                    }

                                    return $options;
                                })
                                ->end()
                                ->useAttributeAsKey('name', false)
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('name')->isRequired()->end()
                                        ->scalarNode('value')->defaultNull()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function createDoctrineMessageBusHookNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('doctrine_message_bus_hook'))
            ->canBeEnabled()
            ->children()
                ->arrayNode('envelope_factory')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('delay')
                            ->canBeEnabled()
                            ->children()
                                ->integerNode('delay_in_milliseconds')->defaultValue(2500)->end()
                            ->end()
                        ->end()
                        ->arrayNode('dispatch_after_current_bus')
                            ->canBeDisabled()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function createVersioningNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('versioning'))
            ->canBeEnabled()
            ->children()
                ->arrayNode('stop_on_new_version')->canBeDisabled()->end()
            ->end();
    }

    private function createRetryNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('retry'))
            ->canBeEnabled()
            ->children()
                ->arrayNode('event_driven')
                    ->canBeEnabled()
                    ->children()
                        ->arrayNode('transports')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function createSerializerEventDispatcherNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('serializer_event_dispatcher'))
            ->canBeEnabled()
            ->children()
                ->arrayNode('decorate_serializers')
                    ->defaultValue([
                        'messenger.transport.native_php_serializer',
                        'messenger.transport.symfony_serializer',
                    ])
                    ->scalarPrototype()->end()
                ->end()
            ->end();
    }

    public function prepend(ContainerBuilder $container, array $config): void
    {
        if (class_exists(Broker::class)) {
            $installationPath = \dirname((new \ReflectionClass(Broker::class))->getFileName(), 2);
            $container->prependExtensionConfig(
                'framework',
                [
                    'translator' => [
                        'paths' => [
                            'draw-messenger' => $installationPath.'/Resources/translations',
                        ],
                    ],
                ]
            );

            if (class_exists($config['entity_class'])) {
                if ($container->hasExtension('doctrine')) {
                    $container->prependExtensionConfig(
                        'doctrine',
                        [
                            'orm' => [
                                'resolve_target_entities' => [
                                    DrawMessageInterface::class => $config['entity_class'],
                                    DrawMessageTagInterface::class => $config['tag_entity_class'],
                                ],
                            ],
                        ]
                    );
                }

                if ($container->hasExtension('draw_sonata_integration')) {
                    $container->prependExtensionConfig(
                        'draw_sonata_integration',
                        [
                            'messenger' => [
                                'admin' => [
                                    'entity_class' => $config['entity_class'],
                                ],
                            ],
                        ]
                    );
                }
            }
        }

        if ($this->isConfigEnabled($container, $config['async_routing_configuration'])) {
            $container->prependExtensionConfig(
                'framework',
                [
                    'messenger' => [
                        'routing' => [
                            AsyncMessageInterface::class => 'async',
                            AsyncHighPriorityMessageInterface::class => 'async_high_priority',
                            AsyncLowPriorityMessageInterface::class => 'async_low_priority',
                        ],
                    ],
                ]
            );
        }
    }
}
