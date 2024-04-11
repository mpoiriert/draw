<?php

namespace Draw\Bundle\SonataImportBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('draw_sonata_import');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->arrayNode('classes')
                    ->beforeNormalization()
                        ->always(function ($classes) {
                            $result = [];
                            foreach ($classes as $class => $configuration) {
                                if (\is_string($configuration)) {
                                    $class = $configuration;
                                    $configuration = ['name' => $class];
                                }

                                if (!isset($configuration['name'])) {
                                    $configuration['name'] = $class;
                                }

                                $result[$class] = $configuration;
                            }

                            return $result;
                        })
                    ->end()
                    ->useAttributeAsKey('name', false)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('alias')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
