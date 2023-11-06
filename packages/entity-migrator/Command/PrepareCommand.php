<?php

namespace Draw\Component\EntityMigrator\Command;

use Draw\Component\EntityMigrator\MigrationInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'draw:entity-migrator:prepare',
    description: 'Prepare migration for all entities',
)]
class PrepareCommand extends BaseCommand
{
    protected function doExecute(InputInterface $input, SymfonyStyle $io, MigrationInterface $migration): int
    {
        $io->info('Preparing migration');
        $result = $this->migrator->prepare($migration);

        $io->success(sprintf('Migration prepared for %d entities', $result));

        return Command::SUCCESS;
    }
}
