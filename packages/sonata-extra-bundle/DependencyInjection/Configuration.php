<?php

namespace Draw\Bundle\SonataExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('draw_sonata_extra');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->append($this->createAutoHelpNode())
                ->append($this->createCanSecurityHandlerNode())
                ->append($this->createFixMenuDepthNode())
                ->append($this->createSessionTimeoutNode())
            ->end();

        return $treeBuilder;
    }

    private function createAutoHelpNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('auto_help'))
            ->canBeEnabled();
    }

    private function createCanSecurityHandlerNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('can_security_handler'))
            ->canBeEnabled()
            ->children()
                ->booleanNode('grant_by_default')->defaultValue(true)->end()
                ->arrayNode('prevent_delete_by_relation')
                    ->canBeEnabled()
                    ->children()
                        ->arrayNode('relations')
                            ->useAttributeAsKey('name', false)
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('class')->isRequired()->end()
                                    ->scalarNode('related_class')->isRequired()->end()
                                    ->scalarNode('path')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function createFixMenuDepthNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('fix_menu_depth'))
            ->canBeEnabled();
    }

    private function createSessionTimeoutNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('session_timeout'))
            ->canBeEnabled()
            ->children()
                ->integerNode('delay')->defaultValue(3600)->end()
            ->end();
    }
}
