<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MessengerTransportNamesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('console.command.messenger_setup_transports')) {
            return;
        }

        $transportNames = $container->getDefinition('console.command.messenger_setup_transports')->getArgument(1);

        $container->setParameter('draw.messenger.transport_names', $transportNames);
    }
}
