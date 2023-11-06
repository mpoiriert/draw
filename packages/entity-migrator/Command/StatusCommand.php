<?php

namespace Draw\Component\EntityMigrator\Command;

use Draw\Component\EntityMigrator\MigrationInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'draw:entity-migrator:status',
    description: 'Show the status of the migration'
)]
class StatusCommand extends BaseCommand
{
    protected function doExecute(InputInterface $input, SymfonyStyle $io, MigrationInterface $migration): int
    {
        $io->info('Migration status');

        $io->table(
            ['State', 'Count'],
            $this->migrator->getStatus($migration)
        );

        return Command::SUCCESS;
    }
}
