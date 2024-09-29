<?php

namespace Draw\Component\AwsToolKit\DependencyInjection\Compiler;

use Draw\Component\AwsToolKit\EventListener\NewestInstanceRoleCheckListener;
use Draw\Component\DependencyInjection\Container\DefinitionFinder;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class AddNewestInstanceRoleCommandOptionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        try {
            $container->findDefinition(NewestInstanceRoleCheckListener::class);
        } catch (ServiceNotFoundException) {
            return;
        }

        foreach (DefinitionFinder::findConsoleCommandDefinitions($container) as $definition) {
            $definition
                ->addMethodCall(
                    'addOption',
                    [
                        NewestInstanceRoleCheckListener::OPTION_AWS_NEWEST_INSTANCE_ROLE,
                        null,
                        InputOption::VALUE_REQUIRED,
                        'The instance role the server must be the newest of to run the command.',
                    ]
                )
            ;
        }
    }
}
