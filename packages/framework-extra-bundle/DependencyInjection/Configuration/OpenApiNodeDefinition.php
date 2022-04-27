<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Configuration;

use Draw\Component\OpenApi\Naming\AliasesClassNamingFilter;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class OpenApiNodeDefinition extends ArrayNodeDefinition
{
    public function __construct()
    {
        parent::__construct('open_api');

        $this
            ->canBeEnabled()
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->createOpenApiNode())
                ->append($this->createRequestNode())
                ->append($this->createResponseNode())
            ->end();
    }

    private function createOpenApiNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('openApi'))
            ->canBeDisabled()
            ->children()
                ->scalarNode('sandbox_url')->defaultValue('/open-api/sandbox')->end()
                ->booleanNode('caching_enabled')->defaultTrue()->end()
                ->booleanNode('cleanOnDump')->defaultTrue()->end()
                ->append($this->createVersioningNode())
                ->append($this->createSchemaNode())
                ->append($this->createHeadersNode())
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
                ->arrayNode('userRequestInterceptedException')
                    ->canBeEnabled()
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
                        ->booleanNode('omitConstraintInvalidValue')->defaultFalse()->end()
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
                ->scalarPrototype()
            ->end();
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

    private function createHeadersNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('headers'))
            ->arrayPrototype()
                ->normalizeKeys(false)
                ->ignoreExtraKeys(false)
            ->end();
    }
}
