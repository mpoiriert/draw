<?php

namespace Draw\Bundle\SonataIntegrationBundle\DependencyInjection;

use App\Entity\MessengerMessage;
use Draw\Bundle\SonataExtraBundle\Configuration\SonataAdminNodeConfiguration;
use Draw\Component\Application\Configuration\Entity\Config;
use Draw\Component\Messenger\Broker;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('draw_sonata_integration');
        $treeBuilder->getRootNode()
            ->children()
                ->append($this->createConfigurationNode())
                ->append($this->createMessengerNode())
            ->end();

        return $treeBuilder;
    }

    private function createConfigurationNode(): ArrayNodeDefinition
    {
        return $this->canBe(Config::class, new ArrayNodeDefinition('configuration'))
            ->canBeEnabled()
            ->append(
                (new SonataAdminNodeConfiguration(Config::class, 'draw.sonata.group.application', 'admin'))
                    ->addDefaultsIfNotSet()
                    ->labelDefaultValue('config')
                    ->iconDefaultValue('fa fa-server')
            );
    }

    private function createMessengerNode(): ArrayNodeDefinition
    {
        return $this->canBe(Broker::class, new ArrayNodeDefinition('messenger'))
            ->children()
                ->arrayNode('queue_names')
                   ->scalarPrototype()->end()
                ->end()
                ->append(
                    (new SonataAdminNodeConfiguration(MessengerMessage::class, 'Messenger', 'admin'))
                        ->addDefaultsIfNotSet()
                        ->pagerTypeDefaultValue('simple')
                        ->iconDefaultValue('fas fa-rss')
                        ->labelDefaultValue('Message')
                )
            ->end();
    }

    private function canBe(string $class, ArrayNodeDefinition $arrayNodeDefinition): ArrayNodeDefinition
    {
        return class_exists($class) ? $arrayNodeDefinition->canBeDisabled() : $arrayNodeDefinition->canBeEnabled();
    }
}
