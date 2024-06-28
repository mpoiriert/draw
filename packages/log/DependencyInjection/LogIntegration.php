<?php

namespace Draw\Component\Log\DependencyInjection;

use Draw\Component\DependencyInjection\Integration\ContainerBuilderIntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationTrait;
use Draw\Component\Log\DependencyInjection\Compiler\LoggerDecoratorPass;
use Draw\Component\Log\Monolog\Processor\DelayProcessor;
use Draw\Component\Log\Symfony\EventListener\SlowRequestLoggerListener;
use Draw\Component\Log\Symfony\Processor\RequestHeadersProcessor;
use Draw\Component\Log\Symfony\Processor\TokenProcessor;
use Symfony\Bridge\Monolog\Processor\ConsoleCommandProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpFoundation\ChainRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher;

class LogIntegration implements IntegrationInterface, ContainerBuilderIntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'log';
    }

    public function buildContainer(ContainerBuilder $container): void
    {
        // It needs to run after the LoggerChannelPass
        $container->addCompilerPass(new LoggerDecoratorPass(), priority: -1);
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
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

        $this->loadSlowRequest($config['slow_request'], $loader, $container);
    }

    private function loadSlowRequest(
        array $config,
        PhpFileLoader $loader,
        ContainerBuilder $container
    ): void {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $container
            ->setDefinition(
                SlowRequestLoggerListener::class,
                (new Definition(SlowRequestLoggerListener::class))
                    ->setAutowired(true)
                    ->setAutoconfigured(true)
            );

        $defaultDuration = $config['default_duration'];
        $requestMatchers = $config['request_matchers'] ?? [];

        if (null !== $defaultDuration && !$requestMatchers) {
            $requestMatchers[] = ['duration' => $defaultDuration];
        }

        $requestMatcherClasses = [
            'path' => RequestMatcher\PathRequestMatcher::class,
            'host' => RequestMatcher\HostRequestMatcher::class,
            'port' => RequestMatcher\PortRequestMatcher::class,
            'schemes' => RequestMatcher\SchemeRequestMatcher::class,
            'ips' => RequestMatcher\IpsRequestMatcher::class,
            'methods' => RequestMatcher\MethodRequestMatcher::class,
        ];

        $requestMatcherReferences = [];
        foreach ($requestMatchers as $requestMatcher) {
            $chainRequestMatcherDefinition = new Definition(ChainRequestMatcher::class);

            $duration = $requestMatcher['duration'] ?? $defaultDuration ?: 10000;

            $requestMatcherReferences[(int) $duration][] = $chainRequestMatcherDefinition;

            unset($requestMatcher['duration']);

            $requestMatcherDefinitions = [];

            ksort($requestMatcher);

            foreach ($requestMatcher as $key => $value) {
                if (null === $value || [] === $value) {
                    continue;
                }

                $requestMatcherClass = $requestMatcherClasses[$key] ?? null;

                if (null === $requestMatcherClass) {
                    throw new \InvalidArgumentException(sprintf('Unknown request matcher "%s".', $key));
                }

                $requestMatcherDefinitions[] = (new Definition($requestMatcherClass))->setArguments([$value]);
            }

            $chainRequestMatcherDefinition->setArgument(
                '$matchers',
                $requestMatcherDefinitions
            );
        }

        $container
            ->getDefinition(SlowRequestLoggerListener::class)
            ->setArgument('$requestMatchers', $requestMatcherReferences);

        $this->renameDefinitions(
            $container,
            SlowRequestLoggerListener::class,
            'draw.log.slow_request_logger_listener'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        $node
             ->children()
                ->arrayNode('slow_request')
                    ->canBeEnabled()
                    ->addDefaultsIfNotSet()
                    ->fixXmlConfig('request_matcher', 'request_matchers')
                    ->children()
                        ->integerNode('default_duration')->min(0)->defaultValue(10000)->end()
                        ->append(
                            $this
                                ->createRequestMatcherNode('request_matchers')
                                    ->children()
                                        ->scalarNode('duration')->end()
                                    ->end()
                                ->end()
                        )
                    ->end()
                ->end()
                ->booleanNode('enable_all_processors')->defaultFalse()->end()
                ->arrayNode('processor')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('console_command')
                            ->canBeEnabled()
                            ->children()
                                ->booleanNode('includeArguments')->defaultTrue()->end()
                                ->booleanNode('includeOptions')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->arrayNode('delay')
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('key')->defaultValue('delay')->end()
                            ->end()
                        ->end()
                        ->arrayNode('request_headers')
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('key')->defaultValue('request_headers')->end()
                                ->arrayNode('onlyHeaders')
                                    ->scalarPrototype()->end()
                                ->end()
                                ->arrayNode('ignoreHeaders')
                                    ->scalarPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('token')
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('key')->defaultValue('token')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function createRequestMatcherNode(string $name, bool $multiple = true): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition($name);
        if ($multiple) {
            $node = $node->prototype('array');
        }

        $node
            ->fixXmlConfig('ip')
            ->fixXmlConfig('method')
            ->fixXmlConfig('scheme')
            ->children()
                ->scalarNode('path')
                    ->defaultNull()
                    ->info('use the urldecoded format')
                    ->example('^/path to resource/')
                ->end()
                ->scalarNode('host')->defaultNull()->end()
                ->integerNode('port')->defaultNull()->end()
                ->arrayNode('schemes')
                    ->beforeNormalization()->ifString()->then(fn ($v) => [$v])->end()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('ips')
                    ->beforeNormalization()->ifString()->then(fn ($v) => [$v])->end()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('methods')
                    ->beforeNormalization()->ifString()->then(fn ($v) => preg_split('/\s*,\s*/', (string) $v))->end()
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $node;
    }
}
