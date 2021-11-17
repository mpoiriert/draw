<?php

namespace Draw\Bundle\CommandBundle\DependencyInjection\Compiler;

use Draw\Bundle\CommandBundle\Listener\CommandFlowListener;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        foreach (array_keys($container->findTaggedServiceIds('console.command')) as $serviceId) {
            $definition = $container->getDefinition($serviceId);
            if ($definition->isAbstract()) {
                continue;
            }

            $definition
                ->addMethodCall(
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
