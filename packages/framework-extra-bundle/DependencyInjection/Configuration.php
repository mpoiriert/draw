<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var IntegrationInterface[]
     */
    private array $integrations;

    public function __construct(array $integrations = [])
    {
        $this->integrations = $integrations;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('draw_framework_extra');

        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->scalarNode('symfony_console_path')->defaultNull()->end();

        foreach ($this->integrations as $integration) {
            $integrationNode = (new ArrayNodeDefinition($integration->getConfigSectionName()))
                ->canBeEnabled();

            $integration->addConfiguration($integrationNode);
            $node->append($integrationNode);
        }

        return $treeBuilder;
    }
}
