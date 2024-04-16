<?php

declare(strict_types=1);

namespace Draw\Component\CronJob;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\CronJob\Entity\CronJob;
use Draw\Component\CronJob\Entity\CronJobExecution;
use Draw\Component\CronJob\Message\ExecuteCronJobMessage;
use Draw\Contracts\Process\ProcessFactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CronJobProcessor
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
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
        $manager = $this->managerRegistry->getManagerForClass(CronJobExecution::class);

        $execution->start();
        $manager->flush();

        $command = $execution->getCronJob()->getCommand();
        $process = $this->processFactory->create(
            [$command],
            timeout: 1800
        );

        try {
            $process->mustRun();

            $execution->end();
        } catch (\Throwable $error) {
            $execution->fail($process->getExitCode(), ['TODO']);
        } finally {
            $manager->flush();
        }
    }
}
