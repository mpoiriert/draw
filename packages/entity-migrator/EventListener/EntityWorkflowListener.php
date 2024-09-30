<?php

namespace Draw\Component\EntityMigrator\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\Entity\EntityMigrationInterface;
use Draw\Component\EntityMigrator\Message\MigrateEntityCommand;
use Draw\Component\EntityMigrator\MigrationInterface;
use Draw\Component\EntityMigrator\Migrator;
use Draw\Component\EntityMigrator\Workflow\EntityMigrationWorkflow;
use Draw\Component\EntityMigrator\Workflow\MigrationWorkflow;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Attribute\AsCompletedListener;
use Symfony\Component\Workflow\Attribute\AsEnteredListener;
use Symfony\Component\Workflow\Attribute\AsGuardListener;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\WorkflowInterface;

class EntityWorkflowListener
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private Migrator $migrator,
        private MessageBusInterface $messageBus,
        #[Autowire(service: MigrationWorkflow::STATE_MACHINE_NAME)]
        private WorkflowInterface $migrationWorkflow,
    ) {
    }

    #[AsGuardListener(EntityMigrationWorkflow::NAME, EntityMigrationWorkflow::TRANSITION_PAUSE)]
    public function canBePaused(GuardEvent $event): void
    {
        if (!$this->getSubject($event)->getMigration()->isPaused()) {
            $event->setBlocked(true, 'Migration is not paused');
        }
    }

    #[AsGuardListener(EntityMigrationWorkflow::NAME, EntityMigrationWorkflow::TRANSITION_SKIP)]
    public function canBeSkip(GuardEvent $event): void
    {
        $subject = $this->getSubject($event);
        $migration = $this->getMigration($event);

        if ($migration->needMigration($subject->getEntity())) {
            $event->setBlocked(true, 'Migration is needed');
        }
    }

    #[AsEnteredListener(EntityMigrationWorkflow::NAME, EntityMigrationWorkflow::PLACE_PROCESSING)]
    public function process(Event $event): void
    {
        $subject = $this->getSubject($event);

        $this
            ->getMigration($event)
            ->migrate($subject->getEntity())
        ;
    }

    #[AsEnteredListener(EntityMigrationWorkflow::NAME, EntityMigrationWorkflow::PLACE_QUEUED)]
    public function queued(Event $event): void
    {
        $this->messageBus->dispatch(
            new MigrateEntityCommand($this->getSubject($event))
        );
    }

    #[AsCompletedListener(EntityMigrationWorkflow::NAME)]
    public function flush(Event $event): void
    {
        $this->managerRegistry
            ->getManagerForClass(
                $this->getSubject($event)->getEntity()::class
            )->flush()
        ;
    }

    #[AsCompletedListener(EntityMigrationWorkflow::NAME, priority: -255)]
    public function updateState(Event $event): void
    {
        $entityMigration = $this->getSubject($event);
        $migration = $entityMigration->getMigration();

        foreach (MigrationWorkflow::finalTransitions() as $transition) {
            if ($this->migrationWorkflow->can($migration, $transition)) {
                $this->migrationWorkflow->apply($migration, $transition);

                return;
            }
        }
    }

    private function getSubject(Event $event): EntityMigrationInterface
    {
        $subject = $event->getSubject();

        \assert($subject instanceof EntityMigrationInterface);

        return $subject;
    }

    private function getMigration(Event $event): MigrationInterface
    {
        return $this->migrator->getMigration($this->getSubject($event)->getMigration()->getName());
    }
}
