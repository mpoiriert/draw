<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Command;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\CronJob\CronJobProcessor;
use Draw\Component\CronJob\Entity\CronJob;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'draw:cron-job:queue-due')]
class QueueDueCronJobsCommand extends Command
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private CronJobProcessor $cronJobProcessor,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Queues due cron jobs');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->section('Queueing cron jobs...');

        $cronJobs = $this->managerRegistry
            ->getRepository(CronJob::class)
            ->findBy(['active' => true]);

        foreach ($cronJobs as $cronJob) {
            if (!$cronJob->isDue()) {
                continue;
            }

            $io->note(sprintf('Queueing cron job "%s"...', $cronJob->getName()));

            $this->cronJobProcessor->queue($cronJob, false);
        }

        $io->success('Cron jobs successfully queued...');

        return Command::SUCCESS;
    }
}
