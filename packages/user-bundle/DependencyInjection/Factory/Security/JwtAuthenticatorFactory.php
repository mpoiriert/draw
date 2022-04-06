<?php

namespace Draw\Bundle\UserBundle\DependencyInjection\Factory\Security;

use Draw\Bundle\UserBundle\Jwt\JwtAuthenticator;
use Draw\Bundle\UserBundle\Jwt\JwtEncoder;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class JwtAuthenticatorFactory implements AuthenticatorFactoryInterface
{
    public function createAuthenticator(
        ContainerBuilder $container,
        string $firewallName,
        array $config,
        string $userProviderId
    ): string {
        $container
            ->setDefinition(
                $serviceId = 'draw.user.jwt_authenticator_'.$firewallName,
                new Definition(JwtAuthenticator::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setArgument('$userProvider', new Reference($userProviderId))
            ->setArgument('$userIdPayloadKey', $config['user_id_payload_key'])
            ->setArgument(
                '$encoder',
                (new Definition(JwtEncoder::class))
                    ->setArgument('$key', $config['key'])
                    ->setArgument('$algorithm', $config['algorithm'])
            );

        if ($serviceAlias = $config['service_alias'] ?? null) {
            $container->setAlias($serviceAlias, $serviceId);
        }

        return $serviceId;
    }

    public function getKey(): string
    {
        return 'draw_jwt_authenticator';
    }

    /**
     * @param NodeDefinition|ArrayNodeDefinition $builder
     */
    public function addConfiguration(NodeDefinition $builder): void
    {
        $builder
            ->children()
                ->scalarNode('provider')->end()
                ->scalarNode('key')->isRequired()->end()
                ->enumNode('algorithm')->values(['HS256'])->isRequired()->end()
                ->scalarNode('service_alias')->defaultNull()->end()
                ->scalarNode('user_id_payload_key')->defaultValue('userId')->end()
            ->end();
    }
}
