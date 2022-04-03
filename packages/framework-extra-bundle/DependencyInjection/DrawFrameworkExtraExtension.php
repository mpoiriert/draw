<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection;

use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\ChangeKeyProcessorDecorator;
use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\RequestHeadersProcessor;
use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\TokenProcessor;
use Draw\Bundle\FrameworkExtraBundle\Logger\SlowRequestLogger;
use Draw\Component\Log\Monolog\Processor\DelayProcessor;
use Draw\Component\Messenger\Message\AsyncHighPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncLowPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncMessageInterface;
use Symfony\Bridge\Monolog\Processor\ConsoleCommandProcessor;
use Symfony\Component\Config\FileLocator;
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

        $this->configureLog($config['log'], $loader, $container);
        $this->configureLogger($config['logger'], $loader, $container);
        $this->configureProcess($config['process'], $loader, $container);
        $this->configureTester($config['tester'], $loader, $container);
    }

    private function configureLog(array $config, PhpFileLoader $loader, ContainerBuilder $containerBuilder): void
    {
        if (!$this->isConfigEnabled($containerBuilder, $config)) {
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
            if (!$enableAllProcessors && !$this->isConfigEnabled($containerBuilder, $processorConfig[$service])) {
                continue;
            }

            $class = $serviceConfiguration['class'];
            $keyDecorator = $serviceConfiguration['keyDecorator'] ?? false;

            $definition = $containerBuilder->setDefinition(
                $serviceName = 'draw.log.'.$service.'_processor',
                new Definition($class)
            );

            $definition->addTag('monolog.processor');

            if (!$keyDecorator) {
                $containerBuilder->setAlias($class, $serviceName);
            }

            $arguments = $processorConfig[$service];

            unset($arguments['enabled']);
            if ($keyDecorator) {
                unset($arguments['key']);
            }

            $definition->setArguments($this->arrayToArgumentsArray($arguments));

            if ($keyDecorator) {
                $containerBuilder->setDefinition(
                    $serviceName.'.key_decorator',
                    new Definition(ChangeKeyProcessorDecorator::class)
                )
                    ->setDecoratedService($serviceName, $serviceName.'.key_decorator..inner')
                    ->setArgument('$decoratedProcessor', new Reference($serviceName.'.key_decorator,inner'))
                    ->setArgument('$key', $processorConfig[$service]['key']);
            }
        }
    }

    private function configureLogger(array $config, PhpFileLoader $loader, ContainerBuilder $containerBuilder): void
    {
        if (!$this->isConfigEnabled($containerBuilder, $config)) {
            return;
        }

        $this->configureSlowRequestLogger($config['slow_request'], $loader, $containerBuilder);
    }

    private function configureSlowRequestLogger(
        array $config,
        PhpFileLoader $loader,
        ContainerBuilder $containerBuilder
    ): void {
        if (!$this->isConfigEnabled($containerBuilder, $config)) {
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

        $containerBuilder->setDefinition(
            'draw.logger.slow_request_logger',
            new Definition(SlowRequestLogger::class)
        )
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$requestMatchers', $requestMatcherReferences);
    }

    private function configureProcess(array $config, PhpFileLoader $loader, ContainerBuilder $containerBuilder): void
    {
        if (!$this->isConfigEnabled($containerBuilder, $config)) {
            return;
        }

        $loader->load('process.php');
    }

    private function configureTester(array $config, PhpFileLoader $loader, ContainerBuilder $containerBuilder): void
    {
        if (!$this->isConfigEnabled($containerBuilder, $config)) {
            return;
        }

        $loader->load('tester.php');
    }

    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('draw_framework_extra');

        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $container->getParameterBag()->resolveValue($configs)
        );

        if (!$this->isConfigEnabled($container, $config['messenger']['async_routing_configuration'])) {
            return;
        }

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

    private function arrayToArgumentsArray(array $arguments): array
    {
        $result = [];
        foreach ($arguments as $key => $value) {
            $result['$'.$key] = $value;
        }

        return $result;
    }
}
