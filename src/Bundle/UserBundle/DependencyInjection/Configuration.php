<?php namespace Draw\Bundle\UserBundle\DependencyInjection;

use App\Entity\User;
use App\Sonata\Admin\UserAdmin;
use Sonata\AdminBundle\SonataAdminBundle;
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
                        ->ifTrue(function ($value) { return !class_exists($value);})
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
            ->{class_exists(SonataDoctrineORMAdminBundle::class) ? 'canBeDisabled' : 'canBeEnabled'}()
            ->children()
                ->scalarNode('user_admin_code')->defaultValue(UserAdmin::class)->end()
            ->end();
    }
}
