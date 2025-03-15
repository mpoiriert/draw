<?php

namespace Draw\Component\EntityMigrator\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parser;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\Message\MigrateEntityCommand;
use Draw\Component\EntityMigrator\Migrator;
use Draw\Component\EntityMigrator\Workflow\EntityMigrationWorkflow;
use Draw\Component\EntityMigrator\Workflow\MigrationWorkflow;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Attribute\AsCompletedListener;
use Symfony\Component\Workflow\Attribute\AsEnteredListener;
use Symfony\Component\Workflow\Attribute\AsGuardListener;
use Symfony\Component\Workflow\Event\EnteredEvent;
use Symfony\Component\Workflow\Event\GuardEvent;

class MigrationWorkflowListener
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private Migrator $migrator,
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[AsGuardListener(MigrationWorkflow::NAME, MigrationWorkflow::TRANSITION_ERROR), ]
    public function canError(GuardEvent $guardEvent): void
    {
        $subject = $guardEvent->getSubject();

        \assert($subject instanceof Migration);

        $entityMigrationClass = $this->getEntityMigrationClass($subject);

        $entityManager = $this->managerRegistry->getManagerForClass($entityMigrationClass);

        \assert($entityManager instanceof EntityManagerInterface);

        if ($this->gotOneNotInState($entityManager, $entityMigrationClass, $subject, EntityMigrationWorkflow::finalPlaces())) {
            $guardEvent->setBlocked(true, 'Some entities still need migration.');

            return;
        }

        if (!$this->gotOneNotInState($entityManager, $entityMigrationClass, $subject, [EntityMigrationWorkflow::PLACE_COMPLETED, EntityMigrationWorkflow::PLACE_SKIPPED])) {
            $guardEvent->setBlocked(true, 'None in error.');
        }
    }

    #[AsGuardListener(MigrationWorkflow::NAME, MigrationWorkflow::TRANSITION_COMPLETE)]
    public function canComplete(GuardEvent $guardEvent): void
    {
        $subject = $guardEvent->getSubject();

        \assert($subject instanceof Migration);

        $entityMigrationClass = $this->getEntityMigrationClass($subject);

        $entityManager = $this->managerRegistry->getManagerForClass($entityMigrationClass);

        \assert($entityManager instanceof EntityManagerInterface);

        if ($this->gotOneNotInState($entityManager, $entityMigrationClass, $subject, EntityMigrationWorkflow::finalPlaces())) {
            $guardEvent->setBlocked(true, 'Some entities still need migration.');

            return;
        }

        if ($this->gotOneNotInState($entityManager, $entityMigrationClass, $subject, [EntityMigrationWorkflow::PLACE_COMPLETED, EntityMigrationWorkflow::PLACE_SKIPPED])) {
            $guardEvent->setBlocked(true, 'One in error.');
        }
    }

    private function getEntityMigrationClass(Migration $migration): string
    {
        $migrationClass = $this->migrator->getMigration($migration->getName())::getTargetEntityClass();

        return \call_user_func([$migrationClass, 'getEntityMigrationClass']);
    }

    private function gotOneNotInState(
        EntityManagerInterface $entityManager,
        string $entityMigrationClass,
        Migration $migration,
        array $states,
    ): bool {
        return null !== $entityManager
            ->createQueryBuilder()
            ->from($entityMigrationClass, 'entity')
            ->andWhere('entity.migration = :migration')
            ->andWhere('entity.state NOT IN (:states)')
            ->setParameter('migration', $migration)
            ->setParameter('states', $states)
            ->select('entity.state')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    #[AsCompletedListener(MigrationWorkflow::NAME)]
    public function flush(): void
    {
        $this->managerRegistry
            ->getManagerForClass(Migration::class)
            ->flush()
        ;
    }

    #[AsEnteredListener(MigrationWorkflow::NAME, MigrationWorkflow::PLACE_PROCESSING)]
    public function process(EnteredEvent $event): void
    {
        /** @var ?ProgressBar $progressBar */
        $progressBar = $event->getContext()['progressBar'] ?? null;
        $subject = $event->getSubject();

        \assert($subject instanceof Migration);

        $migration = $this->migrator->getMigration($subject->getName());

        $manager = $this->managerRegistry->getManagerForClass(Migration::class);

        \assert($manager instanceof EntityManagerInterface);

        $entityMigrationMetadata = $manager->getClassMetadata($this->migrator->getMigrationEntityClass($migration));

        $queryBuilder = $migration->createSelectIdQueryBuilder();

        $query = $queryBuilder
            ->addSelect(
                $queryBuilder->expr()->literal($subject->getId()).' as migration_id',
                $queryBuilder->expr()->literal('{}').' as transition_logs',
                $queryBuilder->expr()->literal(date('Y-m-d H:i:s')).' as created_at',
            )
            ->getQuery()
        ;

        $mappings = array_keys((new Parser($query))->parse()->getParameterMappings());

        $parameters = [];
        $parameterTypes = [];

        foreach ($queryBuilder->getParameters() as $parameter) {
            $index = array_search($parameter->getName(), $mappings, true);

            if (false === $index) {
                throw new \RuntimeException(\sprintf('Parameter [%s] not found in mappings.', $parameter->getName()));
            }

            $parameters[$index] = $parameter->getValue();
            $parameterTypes[$index] = $parameter->getType();
        }

        ksort($parameters);
        ksort($parameterTypes);

        $manager
            ->getConnection()
            ->executeStatement(
                \sprintf(
                    'INSERT IGNORE INTO `%s` (entity_id, migration_id, %s, %s) %s',
                    $entityMigrationMetadata->getTableName(),
                    $entityMigrationMetadata->getColumnName('transitionLogs'),
                    $entityMigrationMetadata->getColumnName('createdAt'),
                    $query->getSQL()
                ),
                $parameters,
                $parameterTypes
            )
        ;

        $queryBuilder = $manager
            ->createQueryBuilder()
            ->from($entityMigrationMetadata->name, 'entity_migration')
            ->andWhere('entity_migration.state = :state')
            ->setParameter('state', EntityMigrationWorkflow::PLACE_NEW)
        ;

        $count = (int) (clone $queryBuilder)
            ->select('count(entity_migration.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $progressBar?->setMaxSteps($count);

        $result = $queryBuilder
            ->select('entity_migration.id as id')
            ->getQuery()
            ->toIterable()
        ;

        foreach ($result as $row) {
            $migrationEntity = $manager->getReference($entityMigrationMetadata->name, $row['id']);

            $this->messageBus->dispatch(
                new MigrateEntityCommand($migrationEntity)
            );

            $progressBar?->advance();
        }
    }
}
