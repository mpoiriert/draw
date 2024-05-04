<?php

declare(strict_types=1);

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

trait ConsolePathAwareCompilerPassTrait
{
    private function setConsolePathArgument(ContainerBuilder $container, string $definitionId): void
    {
        try {
            $definition = $container->findDefinition($definitionId);
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
