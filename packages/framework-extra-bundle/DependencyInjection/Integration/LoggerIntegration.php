<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DrawFrameworkExtraBundle;
use Draw\Bundle\FrameworkExtraBundle\Logger\EventListener\SlowRequestLoggerListener;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpFoundation\RequestMatcher;

class LoggerIntegration implements IntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'logger';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->registerClasses(
            $loader,
            $namespace = 'Draw\\Bundle\\FrameworkExtraBundle\\Logger\\',
            \dirname((new \ReflectionClass(DrawFrameworkExtraBundle::class))->getFileName()).'/Logger'
        );

        $this->loadSlowRequest($config['slow_request'], $loader, $container);

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.logger.'
        );
    }

    private function loadSlowRequest(
        array $config,
        PhpFileLoader $loader,
        ContainerBuilder $container
    ): void {
        if (!$this->isConfigEnabled($container, $config)) {
            $container->removeDefinition(SlowRequestLoggerListener::class);

            return;
        }

        $defaultDuration = $config['default_duration'];
        $requestMatchers = $config['request_matchers'] ?? [];

        if (null !== $defaultDuration && !$requestMatchers) {
            $requestMatchers[] = ['duration' => $defaultDuration];
        }

        $requestMatcherReferences = [];
        foreach ($requestMatchers as $requestMatcher) {
            $requestMatcherDefinition = new Definition(RequestMatcher::class);

            $duration = $requestMatcher['duration'] ?? $defaultDuration ?: 10000;

            $requestMatcherReferences[(int) $duration][] = $requestMatcherDefinition;

            unset($requestMatcher['duration']);

            $requestMatcherDefinition->setArguments(
                $this->arrayToArgumentsArray($requestMatcher)
            );
        }

        $container->getDefinition(SlowRequestLoggerListener::class)
            ->setArgument('$requestMatchers', $requestMatcherReferences);
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
