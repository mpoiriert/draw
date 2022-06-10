<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection;

use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\RequestHeadersProcessor;
use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\TokenProcessor;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\AwsToolKitIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ConfigurationIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ConsoleIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\CronIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\MailerIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\OpenApiIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\PrependIntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ProcessIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\SecurityIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\TesterIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\VersioningIntegration;
use Draw\Bundle\FrameworkExtraBundle\Logger\SlowRequestLogger;
use Draw\Component\Log\Monolog\Processor\DelayProcessor;
use Draw\Component\Messenger\Broker;
use Draw\Component\Messenger\Entity\DrawMessageInterface;
use Draw\Component\Messenger\Entity\DrawMessageTagInterface;
use Draw\Component\Messenger\EventListener\EnvelopeFactoryDelayStampListener;
use Draw\Component\Messenger\EventListener\EnvelopeFactoryDispatchAfterCurrentBusStampListener;
use Draw\Component\Messenger\Message\AsyncHighPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncLowPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncMessageInterface;
use Draw\Component\Security\Jwt\JwtEncoder;
use ReflectionClass;
use Symfony\Bridge\Monolog\Processor\ConsoleCommandProcessor;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpFoundation\RequestMatcher;

class DrawFrameworkExtraExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @var array|IntegrationInterface[]
     */
    private array $integrations = [];

    public function __construct(array $integrations = null)
    {
        if (null === $integrations) {
            $this->registerDefaultIntegrations();
        } else {
            $this->integrations = $integrations;
        }
    }

    private function registerDefaultIntegrations(): void
    {
        $this->integrations[] = new AwsToolKitIntegration();
        $this->integrations[] = new ConfigurationIntegration();
        $this->integrations[] = new ConsoleIntegration();
        $this->integrations[] = new CronIntegration();
        $this->integrations[] = new OpenApiIntegration();
        $this->integrations[] = new MailerIntegration();
        $this->integrations[] = new ProcessIntegration();
        $this->integrations[] = new SecurityIntegration();
        $this->integrations[] = new TesterIntegration();
        $this->integrations[] = new VersioningIntegration();
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration($this->integrations);
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));

        $container->setParameter('draw.symfony_console_path', $config['symfony_console_path']);

        foreach ($this->integrations as $integration) {
            if ($this->isConfigEnabled($container, $config[$integration->getConfigSectionName()])) {
                $integration->load($config[$integration->getConfigSectionName()], $loader, $container);
            }
        }

        $this->configureJwtEncoder($config['jwt_encoder'], $loader, $container);
        $this->configureLog($config['log'], $loader, $container);
        $this->configureLogger($config['logger'], $loader, $container);
        $this->configureMessenger($config['messenger'], $loader, $container);
    }

    private function configureJwtEncoder(
        array $config,
        LoaderInterface $loader,
        ContainerBuilder $container
    ): void {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $container
            ->setDefinition(
                'draw.jwt_encoder',
                new Definition(JwtEncoder::class)
            )
            ->setArgument('$key', $config['key'])
            ->setArgument('$algorithm', $config['algorithm']);

        $container->setAlias(JwtEncoder::class, 'draw.jwt_encoder');
    }

    private function configureLog(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $enableAllProcessors = $config['enable_all_processors'];

        $processorConfig = $config['processor'];

        $services = [
            'console_command' => [
                'class' => ConsoleCommandProcessor::class,
            ],
            'delay' => [
                'class' => DelayProcessor::class,
            ],
            'request_headers' => [
                'class' => RequestHeadersProcessor::class,
            ],
            'token' => [
                'class' => TokenProcessor::class,
            ],
        ];

        foreach ($services as $service => $serviceConfiguration) {
            if (!$enableAllProcessors && !$this->isConfigEnabled($container, $processorConfig[$service])) {
                continue;
            }

            $class = $serviceConfiguration['class'];

            $definition = $container->setDefinition(
                $serviceName = 'draw.log.'.$service.'_processor',
                new Definition($class)
            )
                ->setAutowired(true)
                ->setAutoconfigured(true);

            $definition->addTag('monolog.processor');
            $container->setAlias($class, $serviceName);

            $arguments = $processorConfig[$service];

            unset($arguments['enabled']);

            $definition->setArguments($this->arrayToArgumentsArray($arguments));
        }
    }

    private function configureLogger(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $this->configureSlowRequestLogger($config['slow_request'], $loader, $container);
    }

    private function configureSlowRequestLogger(
        array $config,
        PhpFileLoader $loader,
        ContainerBuilder $container
    ): void {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $defaultDuration = $config['default_duration'];
        $requestMatchers = $config['request_matchers'] ?? [];

        if (null !== $defaultDuration && !$requestMatchers) {
            $requestMatchers[] = ['duration' => $defaultDuration];
        }

        $requestMatcherReferences = [];
        foreach ($requestMatchers as $requestMatcher) {
            $requestMatcherDefinition = new Definition(
                RequestMatcher::class
            );

            $duration = $requestMatcher['duration'] ?? $defaultDuration ?: 10000;

            $requestMatcherReferences[(int) $duration][] = $requestMatcherDefinition;

            unset($requestMatcher['duration']);

            $requestMatcherDefinition->setArguments(
                $this->arrayToArgumentsArray($requestMatcher)
            );
        }

        $container->setDefinition(
            'draw.logger.slow_request_logger',
            new Definition(SlowRequestLogger::class)
        )
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$requestMatchers', $requestMatcherReferences);
    }

    private function configureMessenger(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $loader->load('messenger.php');

        if ($this->isConfigEnabled($container, $config['application_version_monitoring'])) {
            $loader->load('messenger-application-version-monitoring.php');
        }

        if ($this->isConfigEnabled($container, $config['broker'])) {
            $brokerConfig = $config['broker'];
            $loader->load('messenger-broker.php');

            $defaultOptions = [];
            foreach ($brokerConfig['default_options'] as $options) {
                $defaultOptions[$options['name']] = $options['value'];
            }

            $container->getDefinition('draw.messenger.command.start_messenger_broker');

            $container->getDefinition('draw.messenger.broker_default_values_listener')
                ->setArgument('$receivers', $brokerConfig['receivers'])
                ->setArgument('$defaultOptions', $defaultOptions);
        }

        if ($this->isConfigEnabled($container, $config['doctrine_message_bus_hook'])) {
            $loader->load('messenger-doctrine-message-bus-hook.php');
            $envelopeFactoryConfig = $config['doctrine_message_bus_hook']['envelope_factory'];
            if ($this->isConfigEnabled($container, $envelopeFactoryConfig['dispatch_after_current_bus'])) {
                $container
                    ->setDefinition(
                        'draw.messenger.event_listener.envelope_factory_dispatch_after_current_bus_stamp_listener',
                        new Definition(EnvelopeFactoryDispatchAfterCurrentBusStampListener::class)
                    )
                    ->setAutoconfigured(true)
                    ->setAutowired(true);

                $container
                    ->setAlias(
                        EnvelopeFactoryDispatchAfterCurrentBusStampListener::class,
                        'draw.messenger.event_listener.envelope_factory_dispatch_after_current_bus_stamp_listener'
                    );
            }

            if ($this->isConfigEnabled($container, $envelopeFactoryConfig['delay'])) {
                $container
                    ->setDefinition(
                        'draw.messenger.event_listener.envelope_factory_delay_stamp_listener',
                        new Definition(EnvelopeFactoryDelayStampListener::class)
                    )
                    ->setAutoconfigured(true)
                    ->setAutowired(true)
                    ->setArgument('$delay', $envelopeFactoryConfig['delay']['delay_in_milliseconds']);

                $container
                    ->setAlias(
                        EnvelopeFactoryDelayStampListener::class,
                        'draw.messenger.event_listener.envelope_factory_delay_stamp_listener'
                    );
            }
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig('draw_framework_extra');

        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $container->getParameterBag()->resolveValue($configs)
        );

        foreach ($this->integrations as $integration) {
            if (!$integration instanceof PrependIntegrationInterface) {
                continue;
            }

            $integrationConfiguration = $config[$integration->getConfigSectionName()];

            if ($this->isConfigEnabled($container, $integrationConfiguration)) {
                $integration->prepend($container, $integrationConfiguration);
            }
        }

        if ($this->isConfigEnabled($container, $config['messenger'])
            && class_exists(Broker::class)
        ) {
            $installationPath = dirname((new ReflectionClass(Broker::class))->getFileName());
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

            if (class_exists($config['messenger']['entity_class'])) {
                if ($container->hasExtension('doctrine')) {
                    $container->prependExtensionConfig(
                        'doctrine',
                        [
                            'orm' => [
                                'resolve_target_entities' => [
                                    DrawMessageInterface::class => $config['messenger']['entity_class'],
                                    DrawMessageTagInterface::class => $config['messenger']['tag_entity_class'],
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
                                    'entity_class' => $config['messenger']['entity_class'],
                                ],
                            ],
                        ]
                    );
                }
            }
        }

        if ($this->isConfigEnabled($container, $config['messenger']['async_routing_configuration'])) {
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

    private function arrayToArgumentsArray(array $arguments): array
    {
        $result = [];
        foreach ($arguments as $key => $value) {
            $result['$'.$key] = $value;
        }

        return $result;
    }
}
