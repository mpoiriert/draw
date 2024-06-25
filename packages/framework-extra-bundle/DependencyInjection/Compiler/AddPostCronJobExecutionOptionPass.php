<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Draw\Component\CronJob\EventListener\PostExecutionQueueCronJobListener;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class AddPostCronJobExecutionOptionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        try {
            $container->findDefinition(PostExecutionQueueCronJobListener::class);
        } catch (ServiceNotFoundException) {
            return;
        }

        foreach (array_keys($container->findTaggedServiceIds('console.command')) as $serviceId) {
            $definition = $container->getDefinition($serviceId);
            if (!$definition->isAbstract()) {
                $definition
                    ->addMethodCall(
                        'addOption',
                        [
                            PostExecutionQueueCronJobListener::OPTION_POST_EXECUTION_QUEUE_CRON_JOB,
                            null,
                            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                            'Queue does cron job by name after execution of the command.',
                        ]
                    );
            }
        }
    }
}
