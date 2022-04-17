<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MessengerBrokerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('draw.messenger.command.start_messenger_broker')) {
            return;
        }

        if (null === $symfonyConsolePath = $container->getParameter('draw.symfony_console_path')) {
            $symfonyConsolePath = $container->getParameterBag()->resolveValue('%kernel.project_dir%/bin/console');
        }

        if (false === realpath($symfonyConsolePath)) {
            throw new RuntimeException('The draw_framework_extra.symfony_console_path value ['.$symfonyConsolePath.'] is invalid');
        }

        $container
            ->getDefinition('draw.messenger.command.start_messenger_broker')
            ->setArgument('$consolePath', realpath($symfonyConsolePath));
    }
}
