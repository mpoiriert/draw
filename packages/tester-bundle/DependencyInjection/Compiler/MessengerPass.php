<?php

namespace Draw\Bundle\TesterBundle\DependencyInjection\Compiler;

use Draw\Bundle\TesterBundle\Messenger\HandleMessagesMappingProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MessengerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('console.command.messenger_debug')) {
            return;
        }

        $container->getDefinition(HandleMessagesMappingProvider::class)->setArgument(
            0,
            $container->getDefinition('console.command.messenger_debug')->getArgument(0)
        );
    }
}
