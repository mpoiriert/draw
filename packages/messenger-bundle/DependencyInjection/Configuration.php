<?php

namespace Draw\Bundle\MessengerBundle\DependencyInjection;

use App\Entity\MessengerMessage;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('draw_messenger');
        $treeBuilder->getRootNode()
            ->children()
                ->append($this->createSonataNode())
                ->scalarNode('transport_service_name')->defaultValue('messenger.transport.draw')->end()
            ->end();

        return $treeBuilder;
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
                ->scalarNode('group')->defaultValue('Messenger')->end()
                ->scalarNode('controller_class')->defaultValue(CRUDController::class)->end()
                ->scalarNode('icon')->defaultValue('fa fa-rss')->end()
                ->scalarNode('label')->defaultValue('Message')->end()
                ->enumNode('pager_type')->values(['default', 'simple'])->defaultValue('simple')->end()
            ->end();
    }
}
