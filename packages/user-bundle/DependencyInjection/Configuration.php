<?php

namespace Draw\Bundle\UserBundle\DependencyInjection;

use App\Entity\User;
use App\Sonata\Admin\UserAdmin;
use Draw\Bundle\UserBundle\AccountLocker\Entity\UserLock;
use Draw\Bundle\UserBundle\AccountLocker\Sonata\Controller\UserLockController;
use Draw\Bundle\UserBundle\Sonata\Extension\TwoFactorAuthenticationExtension;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\SonataAdminBundle;
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
                ->append($this->createSonataNode())
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
                        ->arrayNode('enforcing_roles')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->append($this->createNeedPasswordChangeEnforcerNode())
                ->append($this->createPasswordRecoveryNode())
                ->arrayNode('email_writers')
                    ->canBeEnabled()
                ->end()

                ->scalarNode('user_entity_class')
                    ->validate()
                        ->ifTrue(function ($value) { return !class_exists($value); })
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

    private function createAccountLockerNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('account_locker'))
            ->canBeEnabled()
            ->children()
                ->scalarNode('account_locked_route')->defaultValue('draw_user_account_locker_account_locked')->end()
                ->arrayNode('entity')
                    ->canBeEnabled()
                ->end()
                ->arrayNode('sonata')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('model_class')->defaultValue(UserLock::class)->end()
                        ->scalarNode('controller')->defaultValue(UserLockController::class)->end()
                        ->scalarNode('group')->defaultValue('User')->end()
                        ->booleanNode('show_in_dashboard')->defaultTrue()->end()
                        ->scalarNode('icon')->defaultValue('fas fa-ban')->end()
                        ->scalarNode('label')->defaultValue('User lock')->end()
                        ->enumNode('pager_type')->values(['default', 'simple'])->defaultValue('simple')->end()
                    ->end()
                ->end()
            ->end();
    }

    private function createNeedPasswordChangeEnforcerNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('password_change_enforcer'))
            ->canBeEnabled()
            ->children()
                ->scalarNode('change_password_route')->defaultValue('admin_change_password')->end()
                ->arrayNode('email')
                    ->canBeEnabled()
                ->end()
            ->end();
    }

    private function createOnboardingNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('onboarding'))
            ->canBeEnabled()
            ->children()
                ->arrayNode('email')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('expiration_delay')->defaultValue('+ 7 days')->end()
                    ->end()
                ->end()
            ->end();
    }

    private function createPasswordRecoveryNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('password_recovery'))
            ->canBeEnabled()
            ->children()
                ->arrayNode('email')
                    ->canBeEnabled()
                ->end()
            ->end();
    }

    private function createSonataNode(): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition('sonata'))
            ->{class_exists(SonataAdminBundle::class) ? 'canBeDisabled' : 'canBeEnabled'}()
            ->children()
                ->scalarNode('user_admin_code')->defaultValue(UserAdmin::class)->end()
                ->arrayNode('2fa')
                    ->canBeEnabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('field_positions')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode(TwoFactorAuthenticationExtension::FIELD_2FA_ENABLED)
                                ->addDefaultsIfNotSet()
                                    ->children()
                                        ->variableNode('list')
                                            ->defaultValue(
                                                defined(ListMapper::class.'NAME_ACTIONS')
                                                    ? ListMapper::NAME_ACTIONS
                                                    : '_action'
                                            )
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
}
