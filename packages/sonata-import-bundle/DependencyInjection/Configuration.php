<?php

namespace Draw\Bundle\SonataImportBundle\DependencyInjection;

use Draw\Bundle\SonataImportBundle\Import\Importer;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('draw_sonata_import');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->scalarNode('skip_value')
                    ->info('Sentinel value that, when present in a CSV cell, preserves the existing value on the entity for that (row, column) pair instead of overwriting it. The check runs before any type coercion (date, etc.).')
                    ->defaultValue(Importer::DEFAULT_SKIP_VALUE)
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('classes')
                    ->beforeNormalization()
                        ->always(static function ($classes) {
                            $result = [];
                            foreach ($classes as $class => $configuration) {
                                if (\is_string($configuration)) {
                                    $class = $configuration;
                                    $configuration = ['name' => $class];
                                }

                                if (!isset($configuration['name'])) {
                                    $configuration['name'] = $class;
                                }

                                $result[$class] = $configuration;
                            }

                            return $result;
                        })
                    ->end()
                    ->useAttributeAsKey('name', false)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('alias')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('handlers')
                    ->append($this->createDoctrineTranslationNode())
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function createDoctrineTranslationNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('doctrine_translation');

        if (class_exists(TranslatableInterface::class)) {
            $node->canBeDisabled();
        } else {
            $node->canBeEnabled();
        }

        return
            $node
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('supported_locales')
                        ->defaultValue([])
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
        ;
    }
}
