<?php

namespace Draw\Bundle\OpenApiBundle\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Draw\Component\OpenApi\Naming\AliasesClassNamingFilter;
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
        $treeBuilder = new TreeBuilder('draw_open_api');

        $treeBuilder->getRootNode()
            ->children()
                ->append($this->createOpenApiNode())
                ->append($this->createDoctrineNode())
                ->append($this->createRequestNode())
                ->append($this->createResponseNode())
            ->end();

        return $treeBuilder;
    }

    private function createOpenApiNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('openApi'))
            ->canBeDisabled()
            ->children()
                ->booleanNode('cleanOnDump')->defaultTrue()->end()
                ->append($this->createVersioningNode())
                ->append($this->createSchemaNode())
                ->append($this->createDefinitionAliasesNode())
                ->append($this->createNamingFiltersNode())
            ->end();
    }

    private function createVersioningNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('versioning'))
            ->canBeEnabled()
            ->children()
                ->arrayNode('versions')
                    ->defaultValue([])
                    ->scalarPrototype()->end()
                ->end()
            ->end();
    }

    private function createRequestNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('request'))
            ->canBeDisabled()
            ->children()
                ->arrayNode('validation')
                    ->children()
                        ->arrayNode('pathPrefixes')
                            ->children()
                                ->scalarNode('query')->defaultValue('$.query')->end()
                                ->scalarNode('body')->defaultValue('$.body')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('queryParameter')
                    ->canBeDisabled()
                ->end()
                ->arrayNode('bodyDeserialization')
                    ->canBeDisabled()
                ->end()
            ->end();
    }

    private function createResponseNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('response'))
            ->canBeDisabled()
            ->children()
                ->booleanNode('serializeNull')->defaultTrue()->end()
                ->arrayNode('exceptionHandler')
                    ->canBeDisabled()
                    ->children()
                        ->booleanNode('useDefaultExceptionsStatusCodes')->defaultTrue()->end()
                        ->booleanNode('ignoreConstraintInvalidValue')->defaultFalse()->end()
                        ->arrayNode('exceptionsStatusCodes')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('class')->isRequired()->end()
                                    ->integerNode('code')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->scalarNode('violationKey')->defaultValue('errors')->end()
                    ->end()
                ->end()
            ->end();
    }

    private function createDoctrineNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('doctrine'))
            ->{class_exists(DoctrineBundle::class) ? 'canBeDisabled' : 'canBeEnabled'}();
    }

    private function createDefinitionAliasesNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('definitionAliases'))
            ->defaultValue([])
            ->arrayPrototype()
                ->children()
                    ->scalarNode('class')->isRequired()->end()
                    ->scalarNode('alias')->isRequired()->end()
                ->end()
            ->end();
    }

    private function createNamingFiltersNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('classNamingFilters'))
            ->defaultValue([AliasesClassNamingFilter::class])
            ->scalarPrototype()->end();
    }

    private function createSchemaNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('schema'))
            ->normalizeKeys(false)
            ->ignoreExtraKeys(false)
            ->children()
                ->arrayNode('info')
                    ->children()
                        ->scalarNode('version')->defaultValue('1.0')->end()
                        ->scalarNode('contact')->end()
                        ->scalarNode('termsOfService')->end()
                        ->scalarNode('description')->end()
                        ->scalarNode('title')->end()
                    ->end()
                ->end()
                ->scalarNode('basePath')->end()
                ->scalarNode('swagger')->defaultValue('2.0')->end()
            ->end();
    }
}
