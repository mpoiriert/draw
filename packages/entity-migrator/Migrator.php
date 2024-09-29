<?php

namespace Draw\Component\EntityMigrator;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\Entity\EntityMigrationInterface;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\Repository\EntityMigrationRepository;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Workflow\WorkflowInterface;

class Migrator
{
    public function __construct(
        private ContainerInterface $migrations,
        private WorkflowInterface $entityMigrationStateMachine,
        private EntityMigrationRepository $entityMigrationRepository,
        private ManagerRegistry $managerRegistry,
        private LockFactory $entityMigratorLockFactory,
        private ?LoggerInterface $entityMigratorLogger,
        private array $migrationNames,
    ) {
    }

    public function queue(EntityMigrationInterface $entity): void
    {
        if ($this->entityMigrationStateMachine->can($entity, 'queue')) {
            $this->entityMigrationStateMachine->apply($entity, 'queue');
        }
    }

    public function migrateEntity(MigrationTargetEntityInterface $entity, string $migrationName): void
    {
        if (class_exists($migrationName)) {
            \assert(is_a($migrationName, MigrationInterface::class, true));

            $migrationName = $migrationName::getName();
        }

        $migration = $this->managerRegistry
            ->getRepository(Migration::class)
            ->findOneBy(['name' => $migrationName])
        ;

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
                ->refresh($entityMigration)
            ;
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

    public function getMigration(string $name): MigrationInterface|BatchPrepareMigrationInterface
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
}
