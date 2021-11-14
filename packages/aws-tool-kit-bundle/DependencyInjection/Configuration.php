<?php

namespace Draw\Bundle\AwsToolKitBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('draw_aws_tool_kit');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->enumNode('imds_version')->values([1, 2, null])->defaultValue(1)->end()
            ->end();

        return $treeBuilder;
    }
}
