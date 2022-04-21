<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection;

use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\ChangeKeyProcessorDecorator;
use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\RequestHeadersProcessor;
use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\TokenProcessor;
use Draw\Bundle\FrameworkExtraBundle\Logger\SlowRequestLogger;
use Draw\Component\Application\Configuration\Entity\Config;
use Draw\Component\Application\Cron\Job;
use Draw\Component\Log\Monolog\Processor\DelayProcessor;
use Draw\Component\Messenger\Broker;
use Draw\Component\Messenger\Entity\DrawMessageInterface;
use Draw\Component\Messenger\Entity\DrawMessageTagInterface;
use Draw\Component\Messenger\Message\AsyncHighPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncLowPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncMessageInterface;
use Draw\Component\Security\Jwt\JwtEncoder;
use ReflectionClass;
use Symfony\Bridge\Monolog\Processor\ConsoleCommandProcessor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\RequestMatcher;

class DrawFrameworkExtraExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));

        $container->setParameter('draw.symfony_console_path', $config['symfony_console_path']);

        $this->configureConfiguration($config['configuration'], $loader, $container);
        $this->configureCron($config['cron'], $loader, $container);
        $this->configureJwtEncoder($config['jwt_encoder'], $loader, $container);
        $this->configureLog($config['log'], $loader, $container);
        $this->configureLogger($config['logger'], $loader, $container);
        $this->configureMessenger($config['messenger'], $loader, $container);
        $this->configureProcess($config['process'], $loader, $container);
        $this->configureSecurity($config['security'], $loader, $container);
        $this->configureTester($config['tester'], $loader, $container);
        $this->configureVersioning($config['versioning'], $loader, $container);
    }

    private function configureConfiguration(
        array $config,
        LoaderInterface $loader,
        ContainerBuilder $container
    ): void {
        if (!$config['enabled']) {
            return;
        }

        $loader->load('configuration.php');
    }

    private function configureCron(
        array $config,
        LoaderInterface $loader,
        ContainerBuilder $container
    ): void {
        if (!$config['enabled']) {
            return;
        }

        $loader->load('cron.php');

        $cronManagerDefinition = $container->getDefinition('draw.cron.manager');
        foreach ($config['jobs'] as $jobData) {
            $jobDefinition = new Definition(Job::class);
            $jobDefinition->setArguments([
                $jobData['name'],
                $jobData['command'],
                $jobData['expression'],
                $jobData['enabled'],
                $jobData['description'],
            ]);

            $jobDefinition->addMethodCall('setOutput', [$jobData['output']]);

            $cronManagerDefinition->addMethodCall('addJob', [$jobDefinition]);
        }
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
                'keyDecorator' => true,
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
            $keyDecorator = $serviceConfiguration['keyDecorator'] ?? false;

            $definition = $container->setDefinition(
                $serviceName = 'draw.log.'.$service.'_processor',
                new Definition($class)
            );

            $definition->addTag('monolog.processor');

            if (!$keyDecorator) {
                $container->setAlias($class, $serviceName);
            }

            $arguments = $processorConfig[$service];

            unset($arguments['enabled']);
            if ($keyDecorator) {
                unset($arguments['key']);
            }

            $definition->setArguments($this->arrayToArgumentsArray($arguments));

            if ($keyDecorator) {
                $container->setDefinition(
                    $serviceName.'.key_decorator',
                    new Definition(ChangeKeyProcessorDecorator::class)
                )
                    ->setDecoratedService($serviceName, $serviceName.'.key_decorator..inner')
                    ->setArgument('$decoratedProcessor', new Reference($serviceName.'.key_decorator,inner'))
                    ->setArgument('$key', $processorConfig[$service]['key']);
            }
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
        }
    }

    private function configureProcess(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $loader->load('process.php');
    }

    private function configureSecurity(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $loader->load('security.php');
    }

    private function configureTester(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $loader->load('tester.php');
    }

    private function configureVersioning(
        array $config,
        LoaderInterface $loader,
        ContainerBuilder $containerBuilder
    ): void {
        if (!$config['enabled']) {
            return;
        }

        $loader->load('versioning.php');
    }

    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('draw_framework_extra');

        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $container->getParameterBag()->resolveValue($configs)
        );

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

        $this->prependConfiguration($config['configuration'], $container);
    }

    private function prependConfiguration(array $config, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $reflection = new ReflectionClass(Config::class);

        $container->prependExtensionConfig(
            'doctrine',
            [
                'orm' => [
                    'mappings' => [
                        'DrawConfiguration' => [
                            'is_bundle' => false,
                            'type' => 'annotation',
                            'dir' => dirname($reflection->getFileName()),
                            'prefix' => $reflection->getNamespaceName(),
                        ],
                    ],
                ],
            ]
        );
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
