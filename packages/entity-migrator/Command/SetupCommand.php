<?php

namespace Draw\Component\EntityMigrator\Command;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\Migrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $manager = $this->managerRegistry->getManagerForClass(Migration::class);
        $repository = $manager->getRepository(Migration::class);

        foreach ($this->migrator->getMigrations() as $name => $migration) {
            if ($repository->findOneBy(['name' => $name])) {
                continue;
            }

            $manager->persist(
                (new Migration())
                    ->setName($name)
                    ->setState('new')
            );
        }

        $manager->flush();

        return 0;
    }
}
