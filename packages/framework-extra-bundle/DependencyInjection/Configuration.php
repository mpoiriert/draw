<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection;

use Draw\Component\Messenger\Transport\DrawTransport;
use Draw\Component\Process\ProcessFactory;
use Draw\Component\Tester\DataTester;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('draw_framework_extra');

        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->append($this->createLogNode())
                ->append($this->createMessengerNode())
                ->append($this->createProcessNode())
                ->append($this->createTesterNode())
            ->end();

        return $treeBuilder;
    }

    private function createLogNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('log'))
            ->canBeEnabled()
            ->children()
                ->booleanNode('enable_all_processors')->defaultFalse()->end()
                ->arrayNode('processor')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('console_command')
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('key')->defaultValue('command')->end()
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

    private function createMessengerNode(): ArrayNodeDefinition
    {
        return $this->canBe(DrawTransport::class, new ArrayNodeDefinition('messenger'))
            ->children()
                ->arrayNode('async_routing_configuration')->canBeEnabled()->end()
            ->end();
    }

    private function createProcessNode(): ArrayNodeDefinition
    {
        return $this->canBe(ProcessFactory::class, new ArrayNodeDefinition('process'));
    }

    private function createTesterNode(): ArrayNodeDefinition
    {
        return $this->canBe(DataTester::class, new ArrayNodeDefinition('tester'));
    }

    private function canBe(string $class, ArrayNodeDefinition $arrayNodeDefinition): ArrayNodeDefinition
    {
        return class_exists($class) ? $arrayNodeDefinition->canBeDisabled() : $arrayNodeDefinition->canBeEnabled();
    }
}
