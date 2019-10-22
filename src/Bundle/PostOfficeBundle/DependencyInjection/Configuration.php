<?php namespace Draw\Bundle\PostOfficeBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder('draw_post_office');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->scalarNode('default_communication_locale')
                    ->defaultValue('en')
                ->end()
                ->arrayNode('default_from')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('email')
                            ->isRequired()
                        ->end()
                        ->scalarNode('name')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
