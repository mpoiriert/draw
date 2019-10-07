<?php namespace Draw\Bundle\TesterBundle\DependencyInjection;

use Draw\Component\Profiling\ProfilerCoordinator;
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
