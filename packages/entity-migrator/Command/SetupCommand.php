<?php

namespace Draw\Component\EntityMigrator\Command;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\Migrator;
use Draw\Component\EntityMigrator\Workflow\MigrationWorkflow;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'draw:entity-migrator:setup',
    description: 'Insert the migration in the database'
)]
class SetupCommand extends Command
{
    public function __construct(
        private Migrator $migrator,
        private ManagerRegistry $managerRegistry,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $manager = $this->managerRegistry->getManagerForClass(Migration::class);
        $repository = $manager->getRepository(Migration::class);

        foreach ($this->migrator->getMigrations() as $name => $migration) {
            $io->info('Setup of migration: '.$name);
            if ($repository->findOneBy(['name' => $name])) {
                $io->note('Migration already exist');
                continue;
            }

            $manager->persist(
                (new Migration())
                    ->setName($name)
                    ->setState(MigrationWorkflow::PLACE_NEW)
            );

            $manager->flush();

            $io->success('Migration created');
        }

        $io->success('All migration setup');

        return Command::SUCCESS;
    }
}
