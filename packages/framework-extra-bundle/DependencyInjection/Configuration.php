<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection;

use App\Entity\MessengerMessage;
use App\Entity\MessengerMessageTag;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\Messenger\Transport\DrawTransport;
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
                ->scalarNode('symfony_console_path')->defaultNull()->end()
                ->append($this->createLogNode())
                ->append($this->createMessengerNode());

        foreach ($this->integrations as $integration) {
            $integrationNode = (new ArrayNodeDefinition($integration->getConfigSectionName()))
                ->canBeEnabled();

            $integration->addConfiguration($integrationNode);
            $node->append($integrationNode);
        }

        return $treeBuilder;
    }

    private function createLogNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('log'))
            ->canBeEnabled()
            ->children()
                ->booleanNode('enable_all_processors')->defaultFalse()->end()
                ->arrayNode('processor')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('console_command')
                            ->canBeEnabled()
                            ->children()
                                ->booleanNode('includeArguments')->defaultTrue()->end()
                                ->booleanNode('includeOptions')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->arrayNode('delay')
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('key')->defaultValue('delay')->end()
                            ->end()
                        ->end()
                        ->arrayNode('request_headers')
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('key')->defaultValue('request_headers')->end()
                                ->arrayNode('onlyHeaders')
                                    ->scalarPrototype()->end()
                                ->end()
                                ->arrayNode('ignoreHeaders')
                                    ->scalarPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('token')
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('key')->defaultValue('token')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function createMessengerNode(): ArrayNodeDefinition
    {
        return $this->canBe(DrawTransport::class, new ArrayNodeDefinition('messenger'))
            ->children()
                ->arrayNode('async_routing_configuration')->canBeEnabled()->end()
                ->scalarNode('entity_class')
                    ->validate()
                        ->ifTrue(function ($value) {
                            return !class_exists($value) && MessengerMessage::class !== $value;
                        })
                        ->thenInvalid('The class [%s] must exists.')
                    ->end()
                    ->defaultValue(MessengerMessage::class)
                ->end()
                ->scalarNode('tag_entity_class')
                    ->validate()
                        ->ifTrue(function ($value) {
                            return !class_exists($value) && MessengerMessageTag::class !== $value;
                        })
                        ->thenInvalid('The class [%s] must exists.')
                    ->end()
                    ->defaultValue(MessengerMessageTag::class)
                ->end()

                ->append($this->createMessengerApplicationVersionMonitoring())
                ->append($this->createMessengerBrokerNode())
                ->append($this->createMessengerDoctrineMessageBusHookNode())
            ->end();
    }

    private function createMessengerApplicationVersionMonitoring(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('application_version_monitoring'))
            ->canBeEnabled();
    }

    private function createMessengerBrokerNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('broker'))
            ->canBeEnabled()
            ->children()
                ->arrayNode('receivers')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('default_options')
                    ->normalizeKeys(false)
                    ->beforeNormalization()
                    ->always(function ($options) {
                        foreach ($options as $name => $configuration) {
                            if (!is_array($configuration)) {
                                $options[$name] = $configuration = ['name' => $name, 'value' => $configuration];
                            }
                            if (is_int($name)) {
                                continue;
                            }
                            if (!isset($configuration['name'])) {
                                $options[$name]['name'] = $name;
                            }
                        }

                        return $options;
                    })
                    ->end()
                    ->useAttributeAsKey('name', false)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('value')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function createMessengerDoctrineMessageBusHookNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('doctrine_message_bus_hook'))
            ->canBeEnabled()
            ->children()
                ->arrayNode('envelope_factory')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('delay')
                            ->canBeEnabled()
                            ->children()
                                ->integerNode('delay_in_milliseconds')->defaultValue(2500)->end()
                            ->end()
                        ->end()
                        ->arrayNode('dispatch_after_current_bus')
                            ->canBeDisabled()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function canBe(string $class, ArrayNodeDefinition $arrayNodeDefinition): ArrayNodeDefinition
    {
        return class_exists($class) ? $arrayNodeDefinition->canBeDisabled() : $arrayNodeDefinition->canBeEnabled();
    }
}
