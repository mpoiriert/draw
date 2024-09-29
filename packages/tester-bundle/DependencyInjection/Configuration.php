<?php

namespace Draw\Bundle\TesterBundle\DependencyInjection;

use Draw\Component\Profiling\ProfilerCoordinator;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('draw_tester');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->arrayNode('profiling')
                    ->{class_exists(ProfilerCoordinator::class) ? 'canBeDisabled' : 'canBeEnabled'}()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
