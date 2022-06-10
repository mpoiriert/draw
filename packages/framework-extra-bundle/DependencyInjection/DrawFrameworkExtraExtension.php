<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection;

use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\RequestHeadersProcessor;
use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\TokenProcessor;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\AwsToolKitIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ConfigurationIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ConsoleIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\CronIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\OpenApiIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\PrependIntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\Logger\SlowRequestLogger;
use Draw\Component\Log\Monolog\Processor\DelayProcessor;
use Draw\Component\Mailer\Command\SendTestEmailCommand;
use Draw\Component\Mailer\EmailWriter\DefaultFromEmailWriter;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Draw\Component\Mailer\EventListener\EmailCssInlinerListener;
use Draw\Component\Mailer\EventListener\EmailSubjectFromHtmlTitleListener;
use Draw\Component\Mailer\EventListener\EmailWriterListener;
use Draw\Component\Mailer\Twig\TranslationExtension;
use Draw\Component\Messenger\Broker;
use Draw\Component\Messenger\Entity\DrawMessageInterface;
use Draw\Component\Messenger\Entity\DrawMessageTagInterface;
use Draw\Component\Messenger\EventListener\EnvelopeFactoryDelayStampListener;
use Draw\Component\Messenger\EventListener\EnvelopeFactoryDispatchAfterCurrentBusStampListener;
use Draw\Component\Messenger\Message\AsyncHighPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncLowPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncMessageInterface;
use Draw\Component\Security\Core\Authentication\SystemAuthenticator;
use Draw\Component\Security\Core\Authentication\SystemAuthenticatorInterface;
use Draw\Component\Security\Core\Listener\CommandLineAuthenticatorListener;
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
use Symfony\Component\Mime\Address;

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
        $this->configureMailer($config['mailer'], $loader, $container);
        $this->configureMessenger($config['messenger'], $loader, $container);
        $this->configureProcess($config['process'], $loader, $container);
        $this->configureSecurity($config['security'], $loader, $container);
        $this->configureTester($config['tester'], $loader, $container);
        $this->configureVersioning($config['versioning'], $loader, $container);
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

    private function configureMailer(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $container
            ->setDefinition(
                'draw.mailer.command.send_test_email_command',
                new Definition(SendTestEmailCommand::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true);

        $container
            ->setAlias(
                SendTestEmailCommand::class,
                'draw.mailer.command.send_test_email_command'
            );

        $container
            ->registerForAutoconfiguration(EmailWriterInterface::class)
            ->addTag(EmailWriterInterface::class);

        $container
            ->setDefinition(
                'draw.mailer.email_writer_listener',
                new Definition(EmailWriterListener::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true);

        $container
            ->setAlias(
                EmailWriterListener::class,
                'draw.mailer.email_writer_listener'
            );

        $container
            ->setDefinition(
                'draw.mailer.twig.translation_extension',
                new Definition(TranslationExtension::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true);

        $container
            ->setAlias(
                TranslationExtension::class,
                'draw.mailer.twig.translation_extension',
            );

        if ($this->isConfigEnabled($container, $config['subject_from_html_title'])) {
            $container
                ->setDefinition(
                    'draw.mailer.email_subject_from_html_title_listener',
                    new Definition(EmailSubjectFromHtmlTitleListener::class)
                )
                ->setAutoconfigured(true)
                ->setAutowired(true);

            $container
                ->setAlias(
                    EmailSubjectFromHtmlTitleListener::class,
                    'draw.mailer.email_subject_from_html_title_listener'
                );
        }

        if ($this->isConfigEnabled($container, $config['css_inliner'])) {
            $container
                ->setDefinition(
                    'draw.mailer.email_css_inline_listener',
                    new Definition(EmailCssInlinerListener::class)
                )
                ->setAutoconfigured(true)
                ->setAutowired(true);

            $container
                ->setAlias(
                    EmailCssInlinerListener::class,
                    'draw.mailer.email_css_inline_listener'
                );
        }

        if ($this->isConfigEnabled($container, $config['default_from'])) {
            $container
                ->setDefinition(
                    'draw.mailer.default_from_email_writer',
                    new Definition(DefaultFromEmailWriter::class)
                )
                ->setAutoconfigured(true)
                ->setAutowired(true)
                ->setArgument(
                    '$defaultFrom',
                    (new Definition(Address::class))
                        ->setArguments([$config['default_from']['email'], $config['default_from']['name'] ?? ''])
                );

            $container
                ->setAlias(
                    DefaultFromEmailWriter::class,
                    'draw.mailer.default_from_email_writer'
                );
        }
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
        if ($this->isConfigEnabled($container, $config['system_authentication'])) {
            $container
                ->setDefinition(
                    'draw.security.system_authentication',
                    (new Definition(SystemAuthenticator::class))
                        ->setArgument('$roles', $config['system_authentication']['roles'])
                        ->setAutoconfigured(true)
                        ->setAutowired(true)
                );

            $container
                ->setAlias(
                    SystemAuthenticatorInterface::class,
                    'draw.security.system_authentication'
                );
        }

        if ($this->isConfigEnabled($container, $config['console_authentication'])) {
            $container
                ->setDefinition(
                    'draw.security.command_line_authenticator_listener',
                    (new Definition(CommandLineAuthenticatorListener::class))
                        ->setAutoconfigured(true)
                        ->setAutowired(true)
                        ->setArgument('$systemAutoLogin', $config['console_authentication']['system_auto_login'])
                );

            $container
                ->setAlias(
                    CommandLineAuthenticatorListener::class,
                    'draw.security.command_line_authenticator_listener'
                );
        }
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

        $this->prependMailer($config['mailer'], $container);

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

    private function prependMailer(array $config, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $installationPath = dirname((new ReflectionClass(EmailWriterInterface::class))->getFileName(), 2);

        $container->prependExtensionConfig(
            'framework',
            [
                'translator' => [
                    'paths' => [
                        'draw-mailer' => $installationPath.'/Resources/translations',
                    ],
                ],
            ]
        );

        if ($container->hasExtension('twig')) {
            $container->prependExtensionConfig(
                'twig',
                [
                    'paths' => [
                        $installationPath.'/Resources/views' => 'draw-mailer',
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
