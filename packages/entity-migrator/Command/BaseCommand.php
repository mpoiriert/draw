<?php

namespace Draw\Component\EntityMigrator\Command;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\Migrator;
use Draw\Component\EntityMigrator\Repository\EntityMigrationRepository;
use Symfony\Component\Console\Command\Command;
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
        protected ?ServicesResetter $servicesResetter = null
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
                    fn (Migration $migration) => $migration->getName(),
                    $this->managerRegistry->getRepository(Migration::class)->findAll()
                )
            );

            $input->setArgument('migration-name', $io->askQuestion($question));
        }
    }
}
