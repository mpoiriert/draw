<?php namespace Draw\Bundle\DashboardBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder('draw_dashboard');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->arrayNode('menu')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('operationId')->end()
                            ->scalarNode('icon')->end()
                            ->scalarNode('label')->end()
                            ->arrayNode('children')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('operationId')->end()
                                        ->scalarNode('icon')->end()
                                        ->scalarNode('label')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('toolbar')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('operationId')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
