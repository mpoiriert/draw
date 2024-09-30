<?php

namespace Draw\Component\EntityMigrator\Command;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\Workflow\MigrationWorkflow;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Workflow\Registry;

#[AsCommand(
    name: 'draw:entity-migrator:migrate',
    description: 'Migrate all entities',
)]
class MigrateCommand extends Command
{
    public function __construct(
        private Registry $workflowRegistry,
        private ManagerRegistry $managerRegistry,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('migration-name', null, 'The migration name to migrate')
        ;
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle(
            $input,
            $output
        );

        $migrationEntity = $this->managerRegistry
            ->getRepository(Migration::class)
            ->findOneBy(['name' => $input->getArgument('migration-name')])
        ;

        $workflow = $this->workflowRegistry->get($migrationEntity, MigrationWorkflow::NAME);

        $blockerList = $workflow->buildTransitionBlockerList($migrationEntity, MigrationWorkflow::TRANSITION_PROCESS);

        if (!$blockerList->isEmpty()) {
            foreach ($blockerList as $blocker) {
                $io->warning('Process is blocked: '.$blocker->getMessage());
            }

            return Command::FAILURE;
        }

        $progressBar = $io->createProgressBar();
        $progressBar->setFormat(ProgressBar::FORMAT_DEBUG);

        $workflow->apply($migrationEntity, MigrationWorkflow::TRANSITION_PROCESS, ['progressBar' => $progressBar]);

        $io->newLine();
        $io->success('Migration started');

        return Command::SUCCESS;
    }
}
