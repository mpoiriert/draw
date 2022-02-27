<?php

namespace Draw\Bundle\AwsToolKitBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('draw_aws_tool_kit');
        $node = $treeBuilder->getRootNode();

        $node
            ->validate()
                ->ifTrue(function (array $config) {
                    switch (true) {
                        case !$config['newest_instance_role_check']['enabled']:
                        case null !== $config['imds_version']:
                            return false;
                    }

                    return true;
                })
                ->thenInvalid('You must define a imds_version since you enabled newest_instance_role_check')
            ->end()
            ->children()
                ->enumNode('imds_version')->values([1, 2, null])->defaultNull()->end()
                ->append($this->createNewestInstanceRoleCheckNode())
            ->end();

        return $treeBuilder;
    }

    private function createNewestInstanceRoleCheckNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('newest_instance_role_check'))
            ->canBeEnabled();
    }
}
