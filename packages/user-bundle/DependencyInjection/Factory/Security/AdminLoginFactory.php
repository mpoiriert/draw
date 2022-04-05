<?php

namespace Draw\Bundle\UserBundle\DependencyInjection\Factory\Security;

use Draw\Bundle\UserBundle\Sonata\Security\AdminLoginAuthenticator;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AdminLoginFactory extends FormLoginFactory
{
    public function __construct()
    {
        parent::__construct();

        $this->addOption('required_role', 'ROLE_SONATA_ADMIN');
    }

    public function getKey(): string
    {
        return 'draw_admin_login';
    }

    /**
     * @param NodeDefinition|ArrayNodeDefinition $node
     */
    public function addConfiguration(NodeDefinition $node): void
    {
        parent::addConfiguration($node);

        $node
            ->children()
                ->scalarNode('login_path')->defaultValue('admin_login')->end()
                ->scalarNode('check_path')->defaultValue('admin_login')->end()
                ->scalarNode('default_target_path')->defaultValue('sonata_admin_dashboard')->end()
                ->scalarNode('username_parameter')->defaultValue('admin_login_form[email]')->end()
                ->scalarNode('password_parameter')->defaultValue('admin_login_form[password]')->end()
                ->scalarNode('required_role')->defaultValue('ROLE_SONATA_ADMIN')->end()
            ->end();
    }

    public function createAuthenticator(ContainerBuilder $container, string $firewallName, array $config, string $userProviderId): string
    {
        $serviceId = parent::createAuthenticator($container, $firewallName, $config, $userProviderId);

        $container->getDefinition($serviceId)->setClass(AdminLoginAuthenticator::class);

        return $serviceId;
    }
}
