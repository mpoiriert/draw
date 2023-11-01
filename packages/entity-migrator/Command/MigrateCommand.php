<?php

namespace Draw\Component\EntityMigrator\Command;

use Doctrine\ORM\EntityManagerInterface;
use Draw\Component\EntityMigrator\Entity\Migration;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'draw:entity-migrator:migrate',
    description: 'Migrate all entities',
)]
class MigrateCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('migration-name', null, 'The migration name to migrate');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle(
            $input,
            $output
        );

        $migration = $this->migrator->getMigration($input->getArgument('migration-name'));

        $migrationEntity = $this->managerRegistry
            ->getRepository(Migration::class)
            ->findOneBy(['name' => $migration::getName()]);

        $count = $migration->countAllThatNeedMigration();

        if (0 === $count) {
            $io->warning('No entity need migration');

            return Command::SUCCESS;
        }

        $manager = $this->managerRegistry->getManagerForClass(Migration::class);

        \assert($manager instanceof EntityManagerInterface);

        $realCount = 0;

        $progress = $io->createProgressBar($count ?? 0);
        $progress->setFormat(ProgressBar::FORMAT_DEBUG);
        foreach ($migration->findAllThatNeedMigration() as $entity) {
            $entityMigration = $this->entityMigrationRepository->load(
                $entity,
                $manager->getReference(Migration::class, $migrationEntity->getId())
            );

            $this->migrator->migrate($entityMigration);

            ++$realCount;

            $progress->advance();

            $manager->clear();

            $this->servicesResetter?->reset();
        }

        $progress->finish();

        $io->newLine();

        $io->success(sprintf(
            'Migration %s processed for %d entities',
            $migration::getName(),
            $realCount
        ));

        return Command::SUCCESS;
    }
}
