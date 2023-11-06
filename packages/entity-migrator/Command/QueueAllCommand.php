<?php

namespace Draw\Component\EntityMigrator\Command;

use Draw\Component\EntityMigrator\MigrationInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'draw:entity-migrator:queue-all',
    description: 'Queue migration for all new entities',
)]
class QueueAllCommand extends BaseCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'How many message queued before we reset the container.', 1)
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, 'How many seconds to sleep between each batch', 0);
    }

    protected function doExecute(InputInterface $input, SymfonyStyle $io, MigrationInterface $migration): int
    {
        $progress = $io->createProgressBar();
        $progress->setFormat(ProgressBar::FORMAT_DEBUG);

        $result = $this->migrator->queueAll(
            $migration,
            (int) $input->getOption('batch-size'),
            (int) $input->getOption('sleep'),
            $progress
        );

        $progress->finish();

        $io->newLine();

        if (0 === $result) {
            $io->warning('No new entity need migration');
        } else {
            $io->success(sprintf(
                'Migration %s queued for %d entities',
                $migration::getName(),
                $result
            ));
        }

        return Command::SUCCESS;
    }
}
