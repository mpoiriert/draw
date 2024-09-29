<?php

namespace Draw\Component\Security\DependencyInjection\Factory;

use Draw\Component\Security\Http\Authenticator\JwtAuthenticator;
use Draw\Component\Security\Jwt\JwtEncoder;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class JwtAuthenticatorFactory implements AuthenticatorFactoryInterface
{
    public function getKey(): string
    {
        return 'draw_jwt';
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function createAuthenticator(
        ContainerBuilder $container,
        string $firewallName,
        array $config,
        string $userProviderId,
    ): string {
        $container
            ->setDefinition(
                $serviceId = 'draw.user.jwt_authenticator_'.$firewallName,
                new Definition(JwtAuthenticator::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setArgument('$userProvider', new Reference($userProviderId))
            ->setArgument('$userIdentifierPayloadKey', $config['user_identifier_payload_key'])
            ->setArgument('$userIdentifierGetter', $config['user_identifier_getter'])
            ->setArgument('$expiration', $config['expiration'])
            ->setArgument(
                '$encoder',
                (new Definition(JwtEncoder::class))
                    ->setArgument('$key', $config['key'])
                    ->setArgument('$algorithm', $config['algorithm'])
                    ->setArgument('$privateKey', $config['private_key'])
                    ->setArgument('$passphrase', $config['passphrase'])
            )
        ;

        if ($serviceAlias = $config['service_alias'] ?? null) {
            $container->setAlias($serviceAlias, $serviceId);
        }

        return $serviceId;
    }

    /**
     * @param NodeDefinition|ArrayNodeDefinition $builder
     */
    public function addConfiguration(NodeDefinition $builder): void
    {
        if (!$builder instanceof ArrayNodeDefinition) {
            throw new \RuntimeException(\sprintf('Invalid class for $builder parameter. Expected [%s] received [%s]', ArrayNodeDefinition::class, $builder::class));
        }

        $builder
            ->children()
                ->scalarNode('provider')->end()
                ->scalarNode('key')->isRequired()->end()
                ->scalarNode('private_key')->defaultValue(null)->end()
                ->scalarNode('passphrase')->defaultValue(null)->end()
                ->enumNode('algorithm')->values(['HS256', 'RS256'])->isRequired()->end()
                ->scalarNode('expiration')->defaultValue('+ 7 days')->end()
                ->scalarNode('service_alias')->defaultNull()->end()
                ->scalarNode('user_identifier_payload_key')->defaultValue('userId')->end()
                ->scalarNode('user_identifier_getter')->defaultValue('getId')->end()
            ->end()
        ;
    }
}
