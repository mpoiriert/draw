<?php

declare(strict_types=1);

namespace Draw\Component\CronJob;

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

    public function queue(CronJob $cronJob, bool $force = false): void
    {
        $manager = $this->managerRegistry->getManagerForClass(CronJobExecution::class);

        $manager->persist($execution = $cronJob->newExecution($force));
        $manager->flush();

        $this->messageBus->dispatch(new ExecuteCronJobMessage($execution));
    }

    public function process(CronJobExecution $execution): void
    {
        $event = $this->eventDispatcher->dispatch(new PreCronJobExecutionEvent($execution));

        if ($event->isExecutionCancelled()) {
            return;
        }

        $manager = $this->managerRegistry->getManagerForClass(CronJobExecution::class);

        $execution->start();
        $manager->flush();

        $process = $this->processFactory->create(
            [
                $this->parameterBag->resolveValue(
                    $event->getCommand()
                ),
            ],
            timeout: 1800
        );

        try {
            $process->mustRun();

            $execution->end();
        } catch (\Throwable $error) {
            $execution->fail(
                $process->getExitCode(),
                (array) $error
            );
        }

        $manager->flush();

        $this->eventDispatcher->dispatch(new PostCronJobExecutionEvent($execution));
    }
}
