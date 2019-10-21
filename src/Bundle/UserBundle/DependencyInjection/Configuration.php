<?php namespace Draw\Bundle\UserBundle\DependencyInjection;

use App\Entity\User;
use App\Sonata\Admin\UserAdmin;
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
                ->arrayNode('sonata')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('user_admin_code')
                            ->defaultValue(UserAdmin::class)
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('encrypt_password_listener')
                    ->defaultTrue()
                ->end()
                ->scalarNode('user_entity_class')
                    ->validate()
                        ->ifTrue(function ($value) { return !class_exists($value);})
                        ->thenInvalid('The class [%s] for the user entity must exists.')
                    ->end()
                    ->defaultValue(User::class)
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
