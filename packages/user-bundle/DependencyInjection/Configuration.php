<?php

namespace Draw\Bundle\UserBundle\DependencyInjection;

use App\Entity\User;
use App\Sonata\Admin\UserAdmin;
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
                ->append($this->createSonataNode())
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
                ->arrayNode('email_writers')
                    ->canBeEnabled()
                ->end()
                ->arrayNode('jwt_authenticator')
                    ->canBeEnabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('key')->isRequired()->end()
                        ->arrayNode('query_parameters')
                            ->canBeDisabled()
                            ->children()
                                ->arrayNode('accepted_keys')
                                    ->defaultValue(['_auth'])
                                    ->scalarPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
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
