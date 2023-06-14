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
                ->booleanNode('install_assets')->defaultTrue()->end()
                ->append($this->createAutoHelpNode())
                ->append($this->createCanSecurityHandlerNode())
                ->append($this->createFixMenuDepthNode())
                ->append($this->createListFieldPriorityNode())
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
                        ->arrayNode('entities')
                            ->beforeNormalization()
                            ->always(function ($config) {
                                foreach (array_keys($config) as $class) {
                                    $config[$class]['class'] = $class;
                                }

                                return $config;
                            })
                            ->end()
                            ->useAttributeAsKey('class', false)
                            ->arrayPrototype()
                                ->children()
                                    ->booleanNode('prevent_delete')->defaultNull()->end()
                                    ->scalarNode('class')->isRequired()->end()
                                    ->arrayNode('relations')
                                        ->arrayPrototype()
                                            ->children()
                                                ->scalarNode('related_class')->isRequired()->end()
                                                ->scalarNode('path')->isRequired()->end()
                                                ->scalarNode('info')->defaultNull()->end()
                                                ->booleanNode('prevent_delete')->defaultTrue()->end()
                                            ->end()
                                        ->end()
                                    ->end()
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

    private function createListFieldPriorityNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('list_field_priority'))
            ->canBeDisabled()
            ->children()
                ->integerNode('default_max_field')->defaultNull()->end()
                ->arrayNode('default_field_priorities')
                    ->beforeNormalization()
                    ->always(function ($config) {
                        foreach ($config as $name => $configuration) {
                            if (!\is_array($configuration)) {
                                $config[$name] = [
                                    'field_name' => $name,
                                    'priority' => $configuration,
                                ];
                            } if (!isset($configuration['field_name'])) {
                                $config[$name]['field_name'] = $name;
                            }
                        }

                        return $config;
                    })
                    ->end()
                    ->useAttributeAsKey('field_name', false)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('field_name')->isRequired()->end()
                            ->integerNode('priority')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
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
