<?php

namespace Draw\Component\EntityMigrator\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\Migrator;
use Draw\Component\EntityMigrator\Repository\EntityMigrationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'draw:entity-migrator:migrate-all',
    description: 'Migrate all entities',
)]
class MigrateAllCommand extends Command
{
    public function __construct(
        private Migrator $migrator,
        private EntityMigrationRepository $entityMigrationRepository,
        private ManagerRegistry $managerRegistry
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('migration-name', null, 'The migration name to migrate')
            ->addOption('now', null, InputOption::VALUE_NONE, 'Execute the migration now');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getArgument('migration-name')) {
            $action = $input->getOption('now') ? 'process' : 'queue';
            $io->block(
                sprintf(
                    'Which migration do you want to %s?',
                    $action
                ),
                null,
                'fg=white;bg=blue',
                ' ',
                true
            );

            $question = new ChoiceQuestion(
                'Select which migration',
                array_map(
                    fn (Migration $migration) => $migration->getName(),
                    $this->managerRegistry->getRepository(Migration::class)->findAll()
                )
            );

            $input->setArgument('migration-name', $io->askQuestion($question));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle(
            $input,
            $output
        );

        $now = (bool) $input->getOption('now');

        $migration = $this->migrator->getMigration($input->getArgument('migration-name'));

        $migrationEntity = $this->managerRegistry
            ->getRepository(Migration::class)
            ->findOneBy(['name' => $migration::getName()]);

        $count = $migration->countAllThatNeedMigration();

        if (0 === $count) {
            $io->warning('No entity need migration');

            return Command::SUCCESS;
        }

        $progress = $io->createProgressBar($count ?? 0);

        $manager = $this->managerRegistry->getManagerForClass(Migration::class);

        \assert($manager instanceof EntityManagerInterface);

        $realCount = 0;
        foreach ($migration->findAllThatNeedMigration() as $entity) {
            $entityMigration = $this->entityMigrationRepository->load(
                $entity,
                $manager->getReference(Migration::class, $migrationEntity->getId())
            );

            if ($now) {
                $this->migrator->migrate($entityMigration);
            } else {
                $this->migrator->queue($entityMigration);
            }

            ++$realCount;

            $progress->advance();

            $manager->clear();
        }

        $progress->finish();

        $io->newLine();

        $io->success(sprintf(
            'Migration %s %s for %d entities',
            $migration::getName(),
            $now ? 'processed' : 'queued',
            $realCount
        ));

        return Command::SUCCESS;
    }
}
