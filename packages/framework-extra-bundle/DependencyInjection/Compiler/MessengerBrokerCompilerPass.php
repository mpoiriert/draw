<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Draw\Component\Messenger\Broker\Command\StartMessengerBrokerCommand;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class MessengerBrokerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        try {
            $definition = $container->findDefinition(StartMessengerBrokerCommand::class);
        } catch (ServiceNotFoundException) {
            return;
        }

        if (null === $symfonyConsolePath = $container->getParameter('draw.symfony_console_path')) {
            $symfonyConsolePath = $container->getParameterBag()->resolveValue('%kernel.project_dir%/bin/console');
        }

        if (false === realpath($symfonyConsolePath)) {
            throw new \RuntimeException('The draw_framework_extra.symfony_console_path value ['.$symfonyConsolePath.'] is invalid');
        }

        $definition->setArgument('$consolePath', realpath($symfonyConsolePath));
    }
}
