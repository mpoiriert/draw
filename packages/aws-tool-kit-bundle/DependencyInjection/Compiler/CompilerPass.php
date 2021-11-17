<?php

namespace Draw\Bundle\AwsToolKitBundle\DependencyInjection\Compiler;

use Draw\Bundle\AwsToolKitBundle\Listener\NewestInstanceRoleListener;
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
                        NewestInstanceRoleListener::OPTION_AWS_NEWEST_INSTANCE_ROLE,
                        null,
                        InputOption::VALUE_REQUIRED,
                        'The instance role the server must be the newest of to run the command.',
                    ]
                );
        }
    }
}
