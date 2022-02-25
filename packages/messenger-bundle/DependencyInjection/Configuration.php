<?php

namespace Draw\Bundle\MessengerBundle\DependencyInjection;

use App\Entity\MessengerMessage;
use App\Entity\MessengerMessageTag;
use Draw\Contracts\Application\VersionVerificationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('draw_messenger');
        $treeBuilder->getRootNode()
            ->children()
                ->append($this->createBrokerNode())
                ->append($this->createWorkerVersionMonitoring())
                ->append($this->createSonataNode())
                ->scalarNode('transport_service_name')->defaultValue('messenger.transport.draw')->end()
            ->end();

        return $treeBuilder;
    }

    private function createBrokerNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('broker'))
            ->canBeEnabled()
            ->children()
                ->scalarNode('symfony_console_path')->defaultNull()->end()
                ->arrayNode('receivers')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('default_options')
                    ->normalizeKeys(false)
                    ->beforeNormalization()
                    ->always(function ($options) {
                        foreach ($options as $name => $configuration) {
                            if (!is_array($configuration)) {
                                $options[$name] = $configuration = ['name' => $name, 'value' => $configuration];
                            }
                            if (is_int($name)) {
                                continue;
                            }
                            if (!isset($configuration['name'])) {
                                $options[$name]['name'] = $name;
                            }
                        }

                        return $options;
                    })
                    ->end()
                    ->useAttributeAsKey('name', false)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('value')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function createWorkerVersionMonitoring(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('worker_version_monitoring'))
            ->canBeEnabled()
            ->children()
                ->scalarNode('version_verification_service')->defaultValue(VersionVerificationInterface::class)->end()
            ->end();
    }

    private function createSonataNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('sonata'))
            ->canBeEnabled()
            ->children()
                ->arrayNode('transports')
                    ->beforeNormalization()
                    ->always(function ($commands) {
                        foreach ($commands as $name => $configuration) {
                            if (is_int($name)) {
                                continue;
                            }
                            if (!isset($configuration['queue_name'])) {
                                $commands[$name]['queue_name'] = $name;
                            }
                        }

                        return $commands;
                    })
                    ->end()
                    ->useAttributeAsKey('queue_name', false)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('queue_name')->isRequired()->end()
                            ->scalarNode('transport_name')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('entity_class')
                    ->validate()
                        ->ifTrue(function ($value) {
                            return !class_exists($value) && MessengerMessage::class !== $value;
                        })
                        ->thenInvalid('The class [%s] for the admin must exists must exists.')
                    ->end()
                    ->defaultValue(MessengerMessage::class)
                ->end()
                ->scalarNode('tag_entity_class')
                    ->validate()
                        ->ifTrue(function ($value) {
                            return !class_exists($value) && MessengerMessageTag::class !== $value;
                        })
                        ->thenInvalid('The class [%s] for the tag.')
                    ->end()
                    ->defaultValue(MessengerMessageTag::class)
                ->end()
                ->scalarNode('group')->defaultValue('Messenger')->end()
                ->scalarNode('controller_class')->defaultNull()->end()
                ->scalarNode('icon')->defaultValue('fas fa-rss')->end()
                ->scalarNode('label')->defaultValue('Message')->end()
                ->enumNode('pager_type')->values(['default', 'simple'])->defaultValue('simple')->end()
            ->end();
    }
}
