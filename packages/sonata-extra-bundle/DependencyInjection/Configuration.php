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

    private function createFixMenuDepthNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('fix_menu_depth'))
            ->canBeEnabled();
    }

    private function createSessionTimeoutNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('timeout'))
            ->canBeEnabled()
            ->children()
                ->integerNode('duration')->defaultValue(3600)->end()
            ->end();
    }
}
