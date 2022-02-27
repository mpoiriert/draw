<?php

namespace Draw\Bundle\AwsToolKitBundle\DependencyInjection\Compiler;

use Draw\Bundle\AwsToolKitBundle\Listener\NewestInstanceRoleCheckListener;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddNewestInstanceRoleCommandOptionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('draw.aws_tool_kit.newest_instance_role_check_listener')) {
            return;
        }

        foreach (array_keys($container->findTaggedServiceIds('console.command')) as $serviceId) {
            $definition = $container->getDefinition($serviceId);
            if (!$definition->isAbstract()) {
                $definition
                    ->addMethodCall(
                        'addOption',
                        [
                            NewestInstanceRoleCheckListener::OPTION_AWS_NEWEST_INSTANCE_ROLE,
                            null,
                            InputOption::VALUE_REQUIRED,
                            'The instance role the server must be the newest of to run the command.',
                        ]
                    );
            }
        }
    }
}
