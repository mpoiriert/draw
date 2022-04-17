<?php

namespace Draw\Bundle\SonataIntegrationBundle\DependencyInjection;

use App\Entity\MessengerMessage;
use Draw\Bundle\SonataExtraBundle\Configuration\SonataAdminNodeConfiguration;
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
                ->append($this->createMessengerNode())
            ->end();

        return $treeBuilder;
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
