<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Factory\Security;

use Draw\Component\Security\Http\Authenticator\MessageAuthenticator;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class MessengerMessageAuthenticatorFactory implements AuthenticatorFactoryInterface
{
    public function createAuthenticator(
        ContainerBuilder $container,
        string $firewallName,
        array $config,
        string $userProviderId
    ): string {
        $container
            ->setDefinition(
                $serviceId = 'draw.user.messenger_message_authenticator_'.$firewallName,
                new Definition(MessageAuthenticator::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setArgument('$userProvider', new Reference($userProviderId))
            ->setArgument('$transport', new Reference($config['transport_service']));

        if ($serviceAlias = $config['service_alias'] ?? null) {
            $container->setAlias($serviceAlias, $serviceId);
        }

        return $serviceId;
    }

    public function getKey(): string
    {
        return 'draw_messenger_message';
    }

    /**
     * @param NodeDefinition|ArrayNodeDefinition $builder
     */
    public function addConfiguration(NodeDefinition $builder): void
    {
        $builder
            ->children()
            ->scalarNode('provider')->end()
            ->scalarNode('transport_service')->defaultValue('manual')->end()
            ->end();
    }
}
