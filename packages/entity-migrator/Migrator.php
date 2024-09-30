<?php

namespace Draw\Component\EntityMigrator;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\Entity\EntityMigrationInterface;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\Repository\EntityMigrationRepository;
use Draw\Component\EntityMigrator\Workflow\EntityMigrationWorkflow;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Workflow\WorkflowInterface;

class Migrator
{
    public function __construct(
        private ContainerInterface $migrations,
        #[Autowire(service: EntityMigrationWorkflow::STATE_MACHINE_NAME)]
        private WorkflowInterface $workflow,
        private EntityMigrationRepository $entityMigrationRepository,
        private ManagerRegistry $managerRegistry,
        private LockFactory $entityMigratorLockFactory,
        private ?LoggerInterface $entityMigratorLogger,
        private array $migrationNames,
    ) {
    }

    public function queue(EntityMigrationInterface $entity): void
    {
        if ($this->workflow->can($entity, 'queue')) {
            $this->workflow->apply($entity, 'queue');
        }
    }

    /**
     * This is called directly from a controller to do a just in time migration.
     */
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

    public function migrate(EntityMigrationInterface $entityMigration, bool $wait = false): void
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
            return;
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

        foreach ([EntityMigrationWorkflow::TRANSITION_PAUSE, EntityMigrationWorkflow::TRANSITION_SKIP, EntityMigrationWorkflow::TRANSITION_PROCESS] as $transition) {
            if (!$this->workflow->can($entityMigration, $transition)) {
                continue;
            }
            try {
                $this->workflow->apply($entityMigration, $transition);

                break;
            } catch (\Throwable $error) {
                $this->workflow->apply($entityMigration, EntityMigrationWorkflow::TRANSITION_FAIL, ['error' => $error]);

                return;
            }
        }

        if ($this->workflow->can($entityMigration, EntityMigrationWorkflow::TRANSITION_COMPLETE)) {
            $this->workflow->apply($entityMigration, EntityMigrationWorkflow::TRANSITION_COMPLETE);
        }
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
     * @return class-string<EntityMigrationInterface>
     */
    public function getMigrationEntityClass(MigrationInterface $migration): string
    {
        $class = $migration::getTargetEntityClass();

        return \call_user_func([$class, 'getEntityMigrationClass']);
    }
}
