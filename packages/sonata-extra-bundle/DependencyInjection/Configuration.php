<?php

namespace Draw\Bundle\SonataExtraBundle\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\Extension\AutoActionExtension;
use Sonata\DoctrineORMAdminBundle\Event\PreObjectDeleteBatchEvent;
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
                ->append($this->createActionableAdminNode())
                ->append($this->createAutoActionNode())
                ->append($this->createAutoHelpNode())
                ->append($this->createBatchDeleteCheckNode())
                ->append($this->createCanSecurityHandlerNode())
                ->append($this->createFixMenuDepthNode())
                ->append($this->createListFieldPriorityNode())
                ->append($this->createNotifierNode())
                ->append($this->createPreventDeleteExtensionNode())
                ->append($this->createSessionTimeoutNode())
                ->append($this->createWorkflowNode())
            ->end()
        ;

        return $treeBuilder;
    }

    private function createAutoHelpNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('auto_help'))
            ->canBeEnabled()
        ;
    }

    private function createBatchDeleteCheckNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('batch_delete_check');

        class_exists(PreObjectDeleteBatchEvent::class)
            ? $node->canBeDisabled()
            : $node->canBeEnabled();

        return $node;
    }

    private function createNotifierNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('notifier'))
            ->canBeEnabled()
        ;
    }

    private function createPreventDeleteExtensionNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('prevent_delete_extension'))
            ->canBeEnabled()
            ->children()
                ->scalarNode('restrict_to_role')->defaultNull()->end()
            ->end()
        ;
    }

    private function createActionableAdminNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('actionable_admin'))
            ->canBeEnabled()
        ;
    }

    private function createAutoActionNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('auto_action'))
            ->canBeEnabled()
            ->children()
                ->arrayNode('ignore_admins')
                    ->defaultValue([])
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('actions')
                    ->defaultValue(AutoActionExtension::DEFAULT_ACTIONS)
                    ->arrayPrototype()
                        ->ignoreExtraKeys(false)
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function createCanSecurityHandlerNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('can_security_handler'))
            ->canBeEnabled()
            ->children()
                ->booleanNode('grant_by_default')->defaultValue(true)->end()
                ->arrayNode('prevent_delete_voter')
                    ->canBeEnabled()
                    ->children()
                        ->booleanNode('use_cache')->defaultTrue()->end()
                        ->booleanNode('use_manager')->defaultTrue()->end()
                        ->booleanNode('prevent_delete_from_all_relations')->defaultFalse()->end()
                        ->arrayNode('entities')
                            ->beforeNormalization()
                            ->always(static function ($config) {
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
                                                ->arrayNode('metadata')
                                                    ->variablePrototype()->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function createFixMenuDepthNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('fix_menu_depth'))
            ->canBeEnabled()
        ;
    }

    private function createListFieldPriorityNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('list_field_priority'))
            ->canBeDisabled()
            ->children()
                ->integerNode('default_max_field')->defaultNull()->end()
                ->arrayNode('default_field_priorities')
                    ->beforeNormalization()
                    ->always(static function ($config) {
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
            ->end()
        ;
    }

    private function createSessionTimeoutNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('session_timeout'))
            ->canBeEnabled()
            ->children()
                ->integerNode('delay')->defaultValue(3600)->end()
            ->end()
        ;
    }

    private function createWorkflowNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('workflow'))
            ->canBeEnabled()
            ->children()
                ->variableNode('sonata_admin_extensions')->defaultValue([])->end()
            ->end()
        ;
    }
}
