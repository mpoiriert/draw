<?php

namespace Draw\Bundle\MessengerBundle\DependencyInjection\Compiler;

use Draw\Component\Messenger\Broker;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BrokerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(Broker::class)) {
            return;
        }

        if (null === $symfonyConsolePath = $container->getParameter('draw.messenger.broker.symfony_console_path')) {
            $symfonyConsolePath = $container->getParameterBag()->resolveValue('%kernel.project_dir%/bin/console');
        }

        if (false === realpath($symfonyConsolePath)) {
            throw new \RuntimeException('The draw_messenger.broker.symfony_console_path value ['.$symfonyConsolePath.'] is invalid');
        }

        $container
            ->getDefinition(Broker::class)
            ->setArgument('$symfonyConsolePath', realpath($symfonyConsolePath));
    }
}
