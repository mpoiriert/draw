<?php

namespace Draw\Component\EntityMigrator\Command;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\MigrationInterface;
use Draw\Component\EntityMigrator\Migrator;
use Draw\Component\EntityMigrator\Repository\EntityMigrationRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class BaseCommand extends Command
{
    public function __construct(
        protected Migrator $migrator,
        protected EntityMigrationRepository $entityMigrationRepository,
        protected ManagerRegistry $managerRegistry,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('migration-name', null, 'The migration name to migrate');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getArgument('migration-name')) {
            $io->block(
                'Which migration ?',
                null,
                'fg=white;bg=blue',
                ' ',
                true
            );

            $question = new ChoiceQuestion(
                'Select which migration',
                $this->migrator->getMigrationNames()
            );

            $input->setArgument('migration-name', $io->askQuestion($question));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $migration = $this->migrator->getMigration($input->getArgument('migration-name'));

        $migrationEntity = $this->managerRegistry
            ->getRepository(Migration::class)
            ->findOneBy(['name' => $migration::getName()]);

        if (null === $migrationEntity) {
            $io->error(
                sprintf(
                    'Migration %s not found in database. Make sure to execute draw:entity-migrator:setup first',
                    $migration::getName()
                )
            );

            return Command::FAILURE;
        }

        return $this->doExecute($input, $io, $migration);
    }

    abstract protected function doExecute(InputInterface $input, SymfonyStyle $io, MigrationInterface $migration): int;
}
