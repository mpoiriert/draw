<?php

declare(strict_types=1);

namespace Draw\Component\CronJob;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\CronJob\Entity\CronJob;
use Draw\Component\CronJob\Entity\CronJobExecution;
use Draw\Component\CronJob\Message\ExecuteCronJobMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Process;

class CronJobProcessor
{
    public function __construct(
        private CronJobExecutionFactory $cronJobExecutionFactory,
        private ManagerRegistry $managerRegistry,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function queue(CronJob $cronJob, bool $force = false): void
    {
        $manager = $this->managerRegistry->getManagerForClass(CronJobExecution::class);

        $manager->persist($execution = $this->cronJobExecutionFactory->create($cronJob, $force));
        $manager->flush();

        $this->messageBus->dispatch(new ExecuteCronJobMessage($execution));
    }

    public function process(CronJobExecution $execution): void
    {
        $manager = $this->managerRegistry->getManagerForClass(CronJobExecution::class);

        $execution->start();
        $manager->flush();

        $command = $execution->getCronJob()->getCommand();

        $process = Process::fromShellCommandline($command)
            ->setTimeout(1800);

        try {
            $process->run();

            $execution->end($process->getExitCode());
        } catch (\Throwable $error) {
            $execution->fail($process->getExitCode(), ['TODO']);
        } finally {
            $manager->flush();
        }
    }
}
