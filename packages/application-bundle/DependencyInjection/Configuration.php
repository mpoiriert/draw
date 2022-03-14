<?php

namespace Draw\Bundle\ApplicationBundle\DependencyInjection;

use Draw\Bundle\ApplicationBundle\Configuration\Entity\Config;
use Draw\Component\Core\Configuration\SonataAdminNodeConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('draw_application');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->append($this->createConfigurationNode())
                ->append($this->createVersioningNode())
            ->end();

        return $treeBuilder;
    }

    private function createConfigurationNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('configuration'))
            ->canBeEnabled()
            ->append(
                (new SonataAdminNodeConfiguration(Config::class, 'draw.sonata.group.application'))
                    ->labelDefaultValue('config')
                    ->iconDefaultValue('fa fa-server')
            );
    }

    private function createVersioningNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('versioning'))
            ->canBeEnabled();
    }
}
