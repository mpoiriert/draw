<?php

namespace Draw\Bundle\CommandBundle\DependencyInjection;

use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\CommandBundle\Authentication\SystemAuthenticator;
use Draw\Bundle\CommandBundle\Sonata\Controller\ExecutionController;
use Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle;
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
        $treeBuilder = new TreeBuilder('draw_command');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->append($this->createDoctrineNode())
                ->append($this->createSonataNode())
                ->append($this->createAuthenticationNode())
                ->arrayNode('commands')
                    ->beforeNormalization()
                        ->always(function ($commands) {
                            foreach ($commands as $name => $configuration) {
                                if (!isset($configuration['name'])) {
                                    $commands[$name]['name'] = $name;
                                }
                            }

                            return $commands;
                        })
                    ->end()
                    ->useAttributeAsKey('name', false)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('commandName')->isRequired()->end()
                            ->scalarNode('label')->defaultValue(null)->end()
                            ->scalarNode('icon')->defaultValue(null)->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    private function createDoctrineNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('doctrine'))
            ->{interface_exists(EntityManagerInterface::class) ? 'canBeDisabled' : 'canBeEnabled'}()
            ->children()
                ->scalarNode('log_execution')->defaultTrue()->end()
            ->end();
    }

    private function createAuthenticationNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('authentication'))
            ->canBeEnabled()
            ->children()
                ->scalarNode('system_authentication_service')->defaultValue(SystemAuthenticator::class)->end()
                ->booleanNode('system_auto_login')->defaultFalse()->end()
            ->end();
    }

    private function createSonataNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('sonata'))
            ->{class_exists(SonataDoctrineORMAdminBundle::class) ? 'canBeDisabled' : 'canBeEnabled'}()
            ->children()
                ->scalarNode('group')->defaultValue('Command')->end()
                ->scalarNode('controller_class')->defaultValue(ExecutionController::class)->end()
                ->scalarNode('icon')->defaultValue("<i class='fa fa-terminal'></i>")->end()
                ->scalarNode('label')->defaultValue('Execution')->end()
                ->enumNode('pager_type')->values(['default', 'simple'])->defaultValue('simple')->end()
            ->end();
    }
}
