<?php namespace Draw\Bundle\CronBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder('draw_cron');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->arrayNode('jobs')
                    ->defaultValue([])
                    ->beforeNormalization()
                        ->always(function ($config) {
                            foreach($config as $name => $configuration) {
                                if(!isset($configuration['name'])) {
                                    $config[$name]['name'] = $name;
                                }
                            }
                            return $config;
                        })
                    ->end()
                    ->useAttributeAsKey('name', false)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')
                                ->validate()
                                    ->ifTrue(function ($value) {
                                        return is_int($value);
                                    })
                                    ->thenInvalid('You must specify a name for the job. Can be via the attribute or the key.')
                                ->end()
                                ->isRequired()
                            ->end()
                            ->scalarNode('description')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('expression')
                                ->isRequired()
                            ->end()
                            ->scalarNode('output')
                                ->defaultValue('>/dev/null 2>&1')
                            ->end()
                            ->scalarNode('command')
                                ->isRequired()
                            ->end()
                            ->booleanNode('enabled')
                                ->defaultValue(true)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
