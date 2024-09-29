<?php

declare(strict_types=1);

namespace Draw\Component\CronJob;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\CronJob\Entity\CronJob;
use Draw\Component\CronJob\Entity\CronJobExecution;
use Draw\Component\CronJob\Event\PostCronJobExecutionEvent;
use Draw\Component\CronJob\Event\PreCronJobExecutionEvent;
use Draw\Component\CronJob\Message\ExecuteCronJobMessage;
use Draw\Contracts\Process\ProcessFactoryInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CronJobProcessor
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private ParameterBagInterface $parameterBag,
        private EventDispatcherInterface $eventDispatcher,
        private ProcessFactoryInterface $processFactory,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function queue(CronJob $cronJob, bool $force): void
    {
        $manager = $this->managerRegistry->getManagerForClass(CronJobExecution::class);

        $manager->persist($execution = $cronJob->newExecution($force));
        $manager->flush();

        $this->messageBus->dispatch(new ExecuteCronJobMessage($execution));
    }

    public function process(CronJobExecution $execution): void
    {
        $manager = $this->managerRegistry->getManagerForClass(CronJobExecution::class);

        \assert($manager instanceof EntityManagerInterface);

        if (!$execution->isExecutable(new \DateTimeImmutable())) {
            $execution->skip();
            $manager->flush();

            return;
        }

        $event = $this->eventDispatcher->dispatch(new PreCronJobExecutionEvent($execution));

        if ($event->isExecutionCancelled()) {
            $execution->skip();
            $manager->flush();

            return;
        }

        $execution->start();
        $manager->flush();

        // This allows long process cron to release connection
        // Also allow issue with server "gone away" to be resolved
        $manager->getConnection()->close();

        $process = $this->processFactory->createFromShellCommandLine(
            $this->parameterBag->resolveValue(
                $event->getCommand()
            ),
            timeout: $execution->getCronJob()->getExecutionTimeout()
        );

        try {
            $process->mustRun();

            $execution->end();
        } catch (\Throwable $error) {
            $execution->fail(
                $process->getExitCode(),
                \sprintf(
                    "Error: %s\nOutput: %s\nError Output: %s\n",
                    $error->getMessage(),
                    $process->getOutput(),
                    $process->getErrorOutput()
                )
            );
        }

        $manager->flush();

        $this->eventDispatcher->dispatch(new PostCronJobExecutionEvent($execution));
    }
}
