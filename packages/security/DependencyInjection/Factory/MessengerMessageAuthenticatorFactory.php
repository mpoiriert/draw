<?php

namespace Draw\Component\Security\DependencyInjection\Factory;

use Draw\Component\Messenger\ManualTrigger\Action\ClickMessageAction;
use Draw\Component\Security\Http\Authenticator\MessageAuthenticator;
use Draw\Contracts\Messenger\EnvelopeFinderInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class MessengerMessageAuthenticatorFactory implements AuthenticatorFactoryInterface
{
    public function getKey(): string
    {
        return 'draw_messenger_message';
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
                $serviceId = 'draw.user.messenger_message_authenticator_'.$firewallName,
                new Definition(MessageAuthenticator::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setArgument('$userProvider', new Reference($userProviderId))
            ->setArgument('$envelopeFinder', new Reference(EnvelopeFinderInterface::class))
            ->setArgument('$requestParameterKey', $config['request_parameter_key']);

        if ($serviceAlias = $config['service_alias'] ?? null) {
            $container->setAlias($serviceAlias, $serviceId);
        }

        return $serviceId;
    }

    public function addConfiguration(NodeDefinition $builder): void
    {
        if (!$builder instanceof ArrayNodeDefinition) {
            throw new \RuntimeException(\sprintf('Invalid class for $builder parameter. Expected [%s] received [%s]', ArrayNodeDefinition::class, $builder::class));
        }

        $builder
            ->children()
                ->scalarNode('provider')->end()
                ->scalarNode('request_parameter_key')->defaultValue(ClickMessageAction::MESSAGE_ID_PARAMETER_NAME)->end()
            ->end();
    }
}
