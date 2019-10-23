<?php namespace Draw\Bundle\MessengerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('draw_messenger');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('transport_service_name')->defaultValue('messenger.transport.draw')->end()
            ->end();
        return $treeBuilder;
    }
}