<?php

namespace Draw\Bundle\OpenApiBundle\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Draw\DrawBundle\Config\Definition\Builder\AllowExtraPropertiesNodeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
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
        $treeBuilder = new TreeBuilder('draw_open_api');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode->setBuilder(new AllowExtraPropertiesNodeBuilder());

        $rootNode
            ->beforeNormalization()
            ->always(
                function ($nodes) {
                    if(!isset($nodes['requestBodyParamConverter'])) {
                        $nodes['requestBodyParamConverter'] = null;
                    }
                    return $nodes;
                }
            )
            ->end()
            ->children()
            ->booleanNode('cleanOnDump')
                ->defaultTrue()
            ->end()
            ->append($this->createDoctrineNode())
            ->booleanNode('convertQueryParameterToAttribute')
                ->defaultFalse()
            ->end()
            ->arrayNode('requestBodyParamConverter')
                ->beforeNormalization()
                    ->always(
                        function ($nodes) {
                            if (is_null($nodes)) {
                                $nodes = ['defaultDeserializationConfiguration' => []];
                            }
                            return $nodes;
                        }
                    )
                ->end()
                ->children()
                    ->arrayNode('defaultDeserializationConfiguration')
                        ->children()
                            ->arrayNode('deserializationGroups')
                                ->prototype('variable')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('responseConverter')
                ->canBeEnabled()
                ->children()
                    ->booleanNode('serializeNull')
                        ->defaultTrue()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('definitionAliases')
                ->defaultValue(array())
                ->arrayPrototype()
                    ->children()
                        ->scalarNode("class")->isRequired()->end()
                        ->scalarNode("alias")->isRequired()->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('schema')
            ->normalizeKeys(false)
            ->acceptExtraKeys(true)
                ->children()
                    ->arrayNode("info")
                        ->children()
                            ->scalarNode("version")->defaultValue("1.0")->end()
                            ->scalarNode("contact")->end()
                            ->scalarNode("termsOfService")->end()
                            ->scalarNode("description")->end()
                            ->scalarNode("title")->end()
                        ->end()
                    ->end()
                    ->scalarNode("basePath")->end()
                    ->scalarNode("swagger")->defaultValue("2.0")->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

    private function createDoctrineNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('doctrine'))
            ->{class_exists(DoctrineBundle::class) ? 'canBeDisabled' : 'canBeEnabled'}();
    }
}
