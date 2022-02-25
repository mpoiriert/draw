<?php

namespace Draw\Bundle\SonataExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
        $treeBuilder = new TreeBuilder('draw_sonata_extra');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->append($this->createAutoHelpNode())
                ->append($this->fixMenuDepthNode())
            ->end();

        return $treeBuilder;
    }

    private function createAutoHelpNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('auto_help'))
            ->canBeEnabled();
    }

    private function fixMenuDepthNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('fix_menu_depth'))
            ->canBeEnabled();
    }
}
