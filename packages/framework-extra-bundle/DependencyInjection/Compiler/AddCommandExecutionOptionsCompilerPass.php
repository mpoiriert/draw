<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Draw\Component\Console\EventListener\CommandFlowListener;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class AddCommandExecutionOptionsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        try {
            $container->findDefinition(CommandFlowListener::class);
        } catch (ServiceNotFoundException $exception) {
            return;
        }

        foreach (array_keys($container->findTaggedServiceIds('console.command')) as $serviceId) {
            $definition = $container->getDefinition($serviceId);
            if ($definition->isAbstract()) {
                continue;
            }

            $definition->addMethodCall(
                'addOption',
                [
                    CommandFlowListener::OPTION_EXECUTION_ID,
                    null,
                    InputOption::VALUE_REQUIRED,
                    'The existing execution id of the command. Use internally by the DrawCommandBundle.',
                ]
            );

            $definition->addMethodCall(
                'addOption',
                [
                    CommandFlowListener::OPTION_IGNORE,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Flag to ignore login of the execution to the databases.',
                ]
            );
        }
    }
}
