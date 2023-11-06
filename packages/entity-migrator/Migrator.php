<?php

namespace Draw\Component\EntityMigrator;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\Entity\BaseEntityMigration;
use Draw\Component\EntityMigrator\Entity\EntityMigrationInterface;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\Message\MigrateEntityCommand;
use Draw\Component\EntityMigrator\Repository\EntityMigrationRepository;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class Migrator
{
    public function __construct(
        private array $migrationNames,
        private ContainerInterface $migrations,
        private WorkflowInterface $entityMigrationStateMachine,
        private EntityMigrationRepository $entityMigrationRepository,
        private ManagerRegistry $managerRegistry,
        private LockFactory $entityMigratorLockFactory,
        private ?LoggerInterface $entityMigratorLogger,
        private MessageBusInterface $messageBus,
        private ?ServicesResetter $servicesResetter = null,
    ) {
    }

    private function getEntityManager(): EntityManagerInterface
    {
        $manager = $this->managerRegistry->getManagerForClass(Migration::class);

        \assert($manager instanceof EntityManagerInterface);

        return $manager;
    }

    private function getEntityMigrationClass(MigrationInterface $migration): string
    {
        $entityManager = $this->getEntityManager();

        $metadata = $entityManager
            ->getMetadataFactory()
            ->getMetadataFor($migration::getTargetEntityClass());

        return $metadata->getReflectionClass()
            ->getMethod('getEntityMigrationClass')
            ->invoke(null);
    }

    private function getMigrationEntity(MigrationInterface $migration): Migration
    {
        $entityManager = $this->getEntityManager();

        $migrationEntity = $entityManager
            ->getRepository(Migration::class)
            ->findOneBy(['name' => $migration::getName()]);

        if (null === $migrationEntity) {
            throw new \RuntimeException(sprintf(
                'Migration "%s" not found',
                $migration::getName()
            ));
        }

        return $migrationEntity;
    }

    public function queueAll(MigrationInterface $migration, int $batchSize = 1, int $sleep = 0, ?ProgressBar $progressBar = null): int
    {
        $entityManager = $this->getEntityManager();

        $entityMigrationClass = $this->getEntityMigrationClass($migration);

        $queryBuilder = $entityManager
            ->createQueryBuilder()
            ->from($entityMigrationClass, 'entity_migration')
            ->andWhere('entity_migration.state = :state')
            ->setParameter('state', BaseEntityMigration::STATE_NEW);

        $count = (int) (clone $queryBuilder)
            ->select('count(entity_migration.id)')
            ->getQuery()
            ->getSingleScalarResult();

        if (0 === $count) {
            return $count;
        }

        $progressBar?->setMaxSteps($count);

        $currentBatch = 0;
        foreach ($queryBuilder->select('entity_migration.id as id')->getQuery()->toIterable() as $row) {
            $migrationEntity = $entityManager->getReference($entityMigrationClass, $row['id']);

            $this->messageBus->dispatch(
                new MigrateEntityCommand($migrationEntity)
            );

            $entityManager->clear();

            $progressBar?->advance();

            ++$currentBatch;

            if ($currentBatch >= $batchSize) {
                $currentBatch = 0;
                $this->servicesResetter?->reset();
                sleep($sleep);
            }
        }

        return $count;
    }

    /**
     * Return the states of the entity migration with the number of entity in each state.
     *
     * @return array<string, int>
     */
    public function getStatus(MigrationInterface $migration): array
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->from($this->getEntityMigrationClass($migration), 'entity_migration')
            ->select('entity_migration.state as state', 'count(entity_migration.id) as total')
            ->where('entity_migration.migration = :migration')
            ->groupBy('entity_migration.state')
            ->setParameter('migration', $this->getMigrationEntity($migration))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return int the number of entity prepared
     */
    public function prepare(MigrationInterface $migration): int
    {
        $migrationEntity = $this->getMigrationEntity($migration);

        $queryBuilder = $migration->createQueryBuilder();

        $sql = $queryBuilder
            ->addSelect(
                $queryBuilder->expr()->literal($migrationEntity->getId()).' as migration_id',
                $queryBuilder->expr()->literal('{}').' as transition_logs',
                $queryBuilder->expr()->literal(date('Y-m-d H:i:s')).' as created_at',
            )
            ->getQuery()
            ->getSQL();

        $entityManager = $this->getEntityManager();

        $tableName = $entityManager
            ->getClassMetadata($this->getEntityMigrationClass($migration))
            ->getTableName();

        return (int) $entityManager
            ->getConnection()
            ->executeStatement(
                sprintf(
                    'INSERT IGNORE INTO `%s` (entity_id, migration_id, transition_logs, created_at) %s',
                    $tableName,
                    $sql
                ),
                array_map(
                    fn (Parameter $parameter) => $parameter->getValue(),
                    $queryBuilder->getParameters()->toArray()
                )
            );
    }

    public function migrateEntity(MigrationTargetEntityInterface $entity, string $migrationName): void
    {
        if (class_exists($migrationName)) {
            \assert(is_a($migrationName, MigrationInterface::class, true));

            $migrationName = $migrationName::getName();
        }

        $migration = $this->managerRegistry
            ->getRepository(Migration::class)
            ->findOneBy(['name' => $migrationName]);

        if (null === $migration) {
            $this->entityMigratorLogger?->warning('Migration "{migration}" not found', ['migration' => $migrationName]);

            return;
        }

        $entityMigration = $this->entityMigrationRepository->load($entity, $migration);

        $this->migrate($entityMigration, true);
    }

    /**
     * @return bool true if a transition was applied, false if the entity is already being migrated
     */
    public function migrate(EntityMigrationInterface $entityMigration, bool $wait = false): bool
    {
        $lock = $this->entityMigratorLockFactory->createLock(
            'entity-migrator-'.$entityMigration->getMigration()->getName().'-'.$entityMigration->getId()
        );

        $acquired = $lock->acquire();

        $this->entityMigratorLogger?->info(
            'Acquire lock to migrate "{migration}" for entity "{entityMigrationId}": {acquired}',
            [
                'migration' => $entityMigration->getMigration()->getName(),
                'entityMigrationId' => $entityMigration->getId(),
                'acquired' => $acquired ? 'true' : 'false',
            ]
        );

        if (!$acquired && !$wait) {
            return false;
        }

        if (!$acquired) {
            // This should happen rarely but it can happen.

            $this->entityMigratorLogger?->info(
                'Wait for lock acquisition to migrate "{migration}" for entity "{entityMigrationId}"',
                [
                    'migration' => $entityMigration->getMigration()->getName(),
                    'entityMigrationId' => $entityMigration->getId(),
                ]
            );

            $lock->acquire(true);
            sleep(1); // Wait for database replication
            $this->managerRegistry
                ->getManagerForClass($entityMigration::class)
                ->refresh($entityMigration);
        }

        $transitionApplied = false;

        foreach (['paused', 'skip', 'process'] as $transition) {
            if (!$this->entityMigrationStateMachine->can($entityMigration, $transition)) {
                continue;
            }

            $transitionApplied = true;
            try {
                $this->entityMigrationStateMachine->apply($entityMigration, $transition);

                break;
            } catch (\Throwable $error) {
                $this->entityMigrationStateMachine->apply($entityMigration, 'fail', ['error' => $error]);

                return true;
            }
        }

        if ($this->entityMigrationStateMachine->can($entityMigration, 'complete')) {
            $transitionApplied = true;
            $this->entityMigrationStateMachine->apply($entityMigration, 'complete');
        }

        return $transitionApplied;
    }

    public function getMigration(string $name): MigrationInterface
    {
        return $this->migrations->get($name);
    }

    /**
     * @return iterable<string, MigrationInterface>
     */
    public function getMigrations(): iterable
    {
        foreach ($this->migrationNames as $name) {
            yield $name => $this->getMigration($name);
        }
    }

    /**
     * @return array<string>
     */
    public function getMigrationNames(): array
    {
        return $this->migrationNames;
    }
}
