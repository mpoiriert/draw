<?php

namespace Draw\Bundle\SonataIntegrationBundle\DependencyInjection;

use App\Entity\MessengerMessage;
use App\Sonata\Admin\UserAdmin;
use Draw\Bundle\SonataExtraBundle\Configuration\SonataAdminNodeConfiguration;
use Draw\Bundle\SonataIntegrationBundle\Console\Controller\ExecutionController;
use Draw\Bundle\SonataIntegrationBundle\User\Extension\TwoFactorAuthenticationExtension;
use Draw\Bundle\UserBundle\DrawUserBundle;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Draw\Component\Application\Configuration\Entity\Config;
use Draw\Component\Console\Entity\Execution;
use Draw\Component\Messenger\Broker\Broker;
use Sonata\AdminBundle\Datagrid\ListMapper;
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
                ->append($this->createConsoleNode())
                ->append($this->createMessengerNode())
                ->append($this->createUserNode())
            ->end();

        return $treeBuilder;
    }

    private function createConfigurationNode(): ArrayNodeDefinition
    {
        return $this->canBe(Config::class, new ArrayNodeDefinition('configuration'))
            ->append(
                (new SonataAdminNodeConfiguration(Config::class, 'draw.sonata.group.application', 'admin'))
                    ->addDefaultsIfNotSet()
                    ->labelDefaultValue('config')
                    ->iconDefaultValue('fa fa-server')
            );
    }

    private function createConsoleNode(): ArrayNodeDefinition
    {
        return $this->canBe(Execution::class, new ArrayNodeDefinition('console'))
            ->append(
                (new SonataAdminNodeConfiguration(Execution::class, 'Command', 'admin'))
                    ->addDefaultsIfNotSet()
                    ->pagerTypeDefaultValue('simple')
                    ->controllerClassDefaultValue(ExecutionController::class)
                    ->labelDefaultValue('Execution')
                    ->iconDefaultValue('fas fa-terminal')
            )
            ->children()
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

    private function createUserNode(): ArrayNodeDefinition
    {
        return $this->canBe(DrawUserBundle::class, new ArrayNodeDefinition('user'))
            ->children()
                ->scalarNode('user_admin_code')->defaultValue(UserAdmin::class)->end()
                ->append($this->createUserLockNode())
                ->arrayNode('2fa')
                    ->canBeEnabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('email')
                            ->canBeEnabled()
                        ->end()
                        ->arrayNode('field_positions')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode(TwoFactorAuthenticationExtension::FIELD_2FA_ENABLED)
                                ->addDefaultsIfNotSet()
                                    ->children()
                                        ->variableNode('list')
                                            ->defaultValue(ListMapper::NAME_ACTIONS)
                                        ->end()
                                        ->variableNode('form')->defaultValue(true)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function createUserLockNode(): ArrayNodeDefinition
    {
        return $this->canBe(DrawUserBundle::class, new ArrayNodeDefinition('user_lock'))
             ->children()
                ->arrayNode('unlock_user_lock_extension')
                    ->canBeDisabled()
                ->end()
                ->arrayNode('refresh_user_lock_extension')
                    ->canBeDisabled()
                ->end()
                ->append(
                    (new SonataAdminNodeConfiguration(UserLock::class, 'User', 'admin'))
                        ->addDefaultsIfNotSet()
                        ->pagerTypeDefaultValue('simple')
                        ->iconDefaultValue('fas fa-ba')
                        ->labelDefaultValue('User lock')
                )
            ->end();
    }

    private function canBe(string $class, ArrayNodeDefinition $arrayNodeDefinition): ArrayNodeDefinition
    {
        return class_exists($class) ? $arrayNodeDefinition->canBeDisabled() : $arrayNodeDefinition->canBeEnabled();
    }
}
