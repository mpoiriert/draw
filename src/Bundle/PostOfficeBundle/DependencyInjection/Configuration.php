<?php namespace Draw\Bundle\PostOfficeBundle\DependencyInjection;

use Pelago\Emogrifier\CssInliner;
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
                ->arrayNode('css_inliner')
                    ->canBeEnabled()
                    ->validate()
                        ->ifTrue(function($value) {
                            return $value['enabled'] && !class_exists(CssInliner::class);
                        })
                        ->thenInvalid('The css inliner is base on the [pelago/emogrifier] package. Install it if you want to enable this feature.')
                    ->end()
                ->end()
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
