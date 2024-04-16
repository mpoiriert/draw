<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Command;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\CronJob\CronJobProcessor;
use Draw\Component\CronJob\Entity\CronJob;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'draw:cron-job:queue-by-name')]
class QueueCronJobByNameCommand extends Command
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
            ->setDescription('Queues cron job by name')
            ->addArgument('name', InputArgument::REQUIRED, 'Cron job name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $cronJob = $this->managerRegistry
            ->getRepository(CronJob::class)
            ->findOneBy(['name' => $input->getArgument('name')]);

        if (null === $cronJob) {
            $io->error('Cron job could not be found.');

            return Command::FAILURE;
        }

        $io->section('Queueing cron job...');

        $this->cronJobProcessor->queue($cronJob, true);

        $io->section('Cron job successfully queued.');

        return Command::SUCCESS;
    }
}
