<?php

namespace Draw\Bundle\UserBundle\DependencyInjection;

use App\Entity\User;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('draw_user');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->append($this->createAccountLockerNode())
                ->append($this->createOnboardingNode())
                ->arrayNode('encrypt_password_listener')
                    ->canBeDisabled()
                    ->children()
                        ->booleanNode('auto_generate_password')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('enforce_2fa')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('enable_route')->defaultValue('admin_app_user_enable-2fa')->end()
                        ->arrayNode('allowed_routes')
                            ->defaultValue(['admin_app_user_disable-2fa'])
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('enforcing_roles')
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('email')
                            ->canBeEnabled()
                        ->end()
                    ->end()
                ->end()
                ->append($this->createNeedPasswordChangeEnforcerNode())
                ->append($this->createEmailWritersNodes())
                ->scalarNode('user_entity_class')
                    ->validate()
                        ->ifTrue(static fn ($value) => !class_exists($value))
                        ->thenInvalid('The class [%s] for the user entity must exists.')
                    ->end()
                    ->defaultValue(User::class)
                ->end()
                ->scalarNode('reset_password_route')
                    ->defaultValue('admin_change_password')
                ->end()
                ->scalarNode('invite_create_account_route')
                    ->defaultValue('home')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function createEmailWritersNodes(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('email_writers'))
            ->canBeEnabled()
            ->children()
                ->arrayNode('forgot_password')
                    ->canBeDisabled()
                ->end()
                ->arrayNode('onboarding')
                    ->canBeDisabled()
                    ->children()
                        ->scalarNode('expiration_delay')->defaultValue('+ 7 days')->end()
                    ->end()
                ->end()
                ->arrayNode('password_change_requested')
                    ->canBeDisabled()
                ->end()
                ->arrayNode('to_user')
                    ->canBeDisabled()
                ->end()
            ->end()
        ;
    }

    private function createAccountLockerNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('account_locker'))
            ->canBeEnabled()
            ->children()
                ->scalarNode('account_locked_route')->defaultValue('draw_user_account_locker_account_locked')->end()
                ->arrayNode('entity')
                    ->canBeEnabled()
                ->end()
            ->end()
        ;
    }

    private function createNeedPasswordChangeEnforcerNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('password_change_enforcer'))
            ->canBeEnabled()
            ->children()
                ->scalarNode('change_password_route')->defaultValue('admin_change_password')->end()
            ->end()
        ;
    }

    private function createOnboardingNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('onboarding'))
            ->canBeEnabled()
        ;
    }
}
