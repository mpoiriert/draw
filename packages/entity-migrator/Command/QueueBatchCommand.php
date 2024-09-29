<?php

namespace Draw\Component\EntityMigrator\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\BatchPrepareMigrationInterface;
use Draw\Component\EntityMigrator\Entity\BaseEntityMigration;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\Message\MigrateEntityCommand;
use Draw\Component\EntityMigrator\Migrator;
use Draw\Component\EntityMigrator\Repository\EntityMigrationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'draw:entity-migrator:queue-batch',
    description: 'Queue migration for all entities',
)]
class QueueBatchCommand extends BaseCommand
{
    public function __construct(
        private MessageBusInterface $messageBus,
        Migrator $migrator,
        EntityMigrationRepository $entityMigrationRepository,
        ManagerRegistry $managerRegistry,
        ?ServicesResetter $servicesResetter = null,
    ) {
        parent::__construct($migrator, $entityMigrationRepository, $managerRegistry, $servicesResetter);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('migration-name', null, 'The migration name to migrate')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle(
            $input,
            $output
        );

        $manager = $this->managerRegistry->getManagerForClass(Migration::class);

        \assert($manager instanceof EntityManagerInterface);

        $migration = $this->migrator->getMigration($input->getArgument('migration-name'));

        if (!$migration instanceof BatchPrepareMigrationInterface) {
            $io->error(\sprintf(
                'Migration %s does not implement %s',
                $migration::getName(),
                BatchPrepareMigrationInterface::class
            ));

            return Command::FAILURE;
        }

        $migrationEntity = $manager
            ->getRepository(Migration::class)
            ->findOneBy(['name' => $migration::getName()])
        ;

        $metadata = $manager
            ->getMetadataFactory()
            ->getMetadataFor($migration::getTargetEntityClass())
        ;

        $entityMigrationClass = $metadata->getReflectionClass()
            ->getMethod('getEntityMigrationClass')
            ->invoke(null)
        ;

        $entityMigrationMetadata = $manager->getClassMetadata($entityMigrationClass);

        $queryBuilder = $migration->createSelectIdQueryBuilder();

        $sql = $queryBuilder
            ->addSelect(
                $queryBuilder->expr()->literal($migrationEntity->getId()).' as migration_id',
                $queryBuilder->expr()->literal('{}').' as transition_logs',
                $queryBuilder->expr()->literal(date('Y-m-d H:i:s')).' as created_at',
            )
            ->getQuery()
            ->getSQL()
        ;

        $manager
            ->getConnection()
            ->executeStatement(
                \sprintf(
                    'INSERT IGNORE INTO `%s` (entity_id, migration_id, transition_logs, created_at) %s',
                    $entityMigrationMetadata->getTableName(),
                    $sql
                ),
                array_map(
                    static fn (Parameter $parameter) => $parameter->getValue(),
                    $queryBuilder->getParameters()->toArray()
                )
            )
        ;

        $queryBuilder = $manager
            ->createQueryBuilder()
            ->from($entityMigrationClass, 'entity_migration')
            ->andWhere('entity_migration.state = :state')
            ->setParameter('state', BaseEntityMigration::STATE_NEW)
        ;

        $count = (int) (clone $queryBuilder)
            ->select('count(entity_migration.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if (0 === $count) {
            $io->warning('No new entity need migration');

            return Command::SUCCESS;
        }

        $result = $queryBuilder
            ->select('entity_migration.id as id')
            ->getQuery()
            ->toIterable()
        ;

        $progress = $io->createProgressBar($count);
        $progress->setFormat(ProgressBar::FORMAT_DEBUG);
        foreach ($result as $row) {
            $migrationEntity = $manager->getReference($entityMigrationClass, $row['id']);

            $this->messageBus->dispatch(
                new MigrateEntityCommand($migrationEntity)
            );

            $manager->clear();

            $progress->advance();
            $this->servicesResetter?->reset();
        }

        $progress->finish();

        $io->newLine();

        $io->success(\sprintf(
            'Migration %s queued for %d entities',
            $migration::getName(),
            $count
        ));

        return Command::SUCCESS;
    }
}
