<?php

namespace Draw\Component\EntityMigrator\Command;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\Migrator;
use Draw\Component\EntityMigrator\Repository\EntityMigrationRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;

abstract class BaseCommand extends Command
{
    public function __construct(
        protected Migrator $migrator,
        protected EntityMigrationRepository $entityMigrationRepository,
        protected ManagerRegistry $managerRegistry,
        protected ?ServicesResetter $servicesResetter = null,
    ) {
        parent::__construct();
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
                array_map(
                    static fn (Migration $migration) => $migration->getName(),
                    $this->managerRegistry->getRepository(Migration::class)->findAll()
                )
            );

            $input->setArgument('migration-name', $io->askQuestion($question));
        }
    }

    protected function getMigrationName(InputInterface $input, OutputInterface $output): string
    {
        $io = new SymfonyStyle($input, $output);

        $migrationName = $input->getArgument('migration-name');

        $migrationNames = array_map(
            static fn (Migration $migration) => $migration->getName(),
            $this->managerRegistry
                ->getRepository(Migration::class)
                ->findAll(),
        );

        sort($migrationNames);

        if (null !== $migrationName) {
            if (\in_array($migrationName, $migrationNames, true)) {
                return $migrationName;
            }

            $io->warning(\sprintf('Migration [%s] is invalid.', $migrationName));

            $migrationName = null;
        }

        if (null === $migrationName) {
            $helper = $this->getHelper('question');

            \assert($helper instanceof QuestionHelper);

            $question = new ChoiceQuestion(
                'Which migration you want to execute?',
                $migrationNames,
            );

            $question->setErrorMessage(\sprintf('Migration [%s] is invalid.', $migrationName));

            $migrationName = $helper->ask($input, $output, $question);
        }

        $io->note(\sprintf('Using migration [%s]', $migrationName));

        return $migrationName;
    }
}
