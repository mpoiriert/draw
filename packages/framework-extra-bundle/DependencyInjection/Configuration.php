<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection;

use App\Entity\MessengerMessage;
use App\Entity\MessengerMessageTag;
use Draw\Component\Application\CronManager;
use Draw\Component\Messenger\Transport\DrawTransport;
use Draw\Component\Process\ProcessFactory;
use Draw\Component\Security\Http\EventListener\RoleRestrictedAuthenticatorListener;
use Draw\Component\Tester\DataTester;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
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
                ->append($this->createAwsToolKitNode())
                ->append($this->createConfigurationNode())
                ->append($this->createCronNode())
                ->append($this->createJwtEncoder())
                ->append($this->createLogNode())
                ->append($this->createLoggerNode())
                ->append($this->createMessengerNode())
                ->append($this->createProcessNode())
                ->append($this->createSecurityNode())
                ->append($this->createTesterNode())
                ->append($this->createVersioningNode())
            ->end();

        return $treeBuilder;
    }

    private function createAwsToolKitNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('aws_tool_kit'))
            ->canBeEnabled()
            ->validate()
                ->ifTrue(function (array $config) {
                    switch (true) {
                        case !$config['newest_instance_role_check']['enabled']:
                        case null !== $config['imds_version']:
                            return false;
                    }

                    return true;
                })
                ->thenInvalid('You must define a imds_version since you enabled newest_instance_role_check')
            ->end()
            ->children()
                ->enumNode('imds_version')->values([1, 2, null])->defaultNull()->end()
                ->arrayNode('newest_instance_role_check')
                    ->canBeEnabled()
                ->end()
            ->end();
    }

    private function createConfigurationNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('configuration'))
            ->canBeEnabled();
    }

    private function createCronNode(): ArrayNodeDefinition
    {
        return $this->canBe(CronManager::class, new ArrayNodeDefinition('cron'))
            ->children()
                ->arrayNode('jobs')
                    ->defaultValue([])
                    ->beforeNormalization()
                        ->always(function ($config) {
                            foreach ($config as $name => $configuration) {
                                if (!isset($configuration['name'])) {
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
    }

    private function createVersioningNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('versioning'))
            ->canBeEnabled();
    }

    private function createJwtEncoder(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('jwt_encoder'))
            ->canBeEnabled()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('key')->isRequired()->end()
                ->enumNode('algorithm')->values(['HS256'])->defaultValue('HS256')->end()
            ->end();
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
                                ->scalarNode('key')->defaultValue('command')->end()
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

    private function createLoggerNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('logger'))
            ->canBeEnabled()
            ->children()
                ->arrayNode('slow_request')
                    ->canBeEnabled()
                    ->addDefaultsIfNotSet()
                    ->fixXmlConfig('request_matcher', 'request_matchers')
                    ->children()
                        ->integerNode('default_duration')->min(0)->defaultValue(10000)->end()
                        ->append(
                            $this
                                ->createRequestMatcherNode('request_matchers')
                                    ->children()
                                        ->scalarNode('duration')->end()
                                    ->end()
                                ->end()
                        )
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
            ->canBeEnabled();
    }

    private function createProcessNode(): ArrayNodeDefinition
    {
        return $this->canBe(ProcessFactory::class, new ArrayNodeDefinition('process'));
    }

    private function createSecurityNode(): ArrayNodeDefinition
    {
        return $this->canBe(RoleRestrictedAuthenticatorListener::class, new ArrayNodeDefinition('security'));
    }

    private function createTesterNode(): ArrayNodeDefinition
    {
        return $this->canBe(DataTester::class, new ArrayNodeDefinition('tester'));
    }

    private function canBe(string $class, ArrayNodeDefinition $arrayNodeDefinition): ArrayNodeDefinition
    {
        return class_exists($class) ? $arrayNodeDefinition->canBeDisabled() : $arrayNodeDefinition->canBeEnabled();
    }

    private function createRequestMatcherNode(string $name, bool $multiple = true): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition($name);
        if ($multiple) {
            $node = $node->prototype('array');
        }

        $node
            ->fixXmlConfig('ip')
            ->fixXmlConfig('method')
            ->fixXmlConfig('scheme')
            ->children()
                ->scalarNode('path')
                    ->defaultNull()
                    ->info('use the urldecoded format')
                    ->example('^/path to resource/')
                ->end()
                ->scalarNode('host')->defaultNull()->end()
                ->integerNode('port')->defaultNull()->end()
                ->arrayNode('schemes')
                    ->beforeNormalization()->ifString()->then(function ($v) { return [$v]; })->end()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('ips')
                    ->beforeNormalization()->ifString()->then(function ($v) { return [$v]; })->end()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('methods')
                    ->beforeNormalization()->ifString()->then(function ($v) { return preg_split('/\s*,\s*/', $v); })->end()
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $node;
    }
}
