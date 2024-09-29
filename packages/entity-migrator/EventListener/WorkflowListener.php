<?php

namespace Draw\Component\EntityMigrator\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\Entity\EntityMigrationInterface;
use Draw\Component\EntityMigrator\Message\MigrateEntityCommand;
use Draw\Component\EntityMigrator\MigrationInterface;
use Draw\Component\EntityMigrator\Migrator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;

class WorkflowListener
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private Migrator $migrator,
        private MessageBusInterface $messageBus,
    ) {
    }

    #[AsEventListener('workflow.entity_migration.guard.pause')]
    public function canBePaused(GuardEvent $event): void
    {
        if (!$this->getSubject($event)->getMigration()->isPaused()) {
            $event->setBlocked(true, 'Migration is not paused');
        }
    }

    #[AsEventListener('workflow.entity_migration.guard.skip')]
    public function canBeSkip(GuardEvent $event): void
    {
        $subject = $this->getSubject($event);
        $migration = $this->getMigration($event);

        if ($migration->needMigration($subject->getEntity())) {
            $event->setBlocked(true, 'Migration is needed');
        }
    }

    #[AsEventListener('workflow.entity_migration.guard.process')]
    public function canBeProcess(GuardEvent $event): void
    {
        // lock the process using locker
    }

    #[AsEventListener('workflow.entity_migration.entered.processing')]
    public function process(Event $event): void
    {
        $subject = $this->getSubject($event);

        $this
            ->getMigration($event)
            ->migrate($subject->getEntity());
    }

    #[AsEventListener('workflow.entity_migration.completed.queue')]
    public function queued(Event $event): void
    {
        $this->messageBus->dispatch(
            new MigrateEntityCommand($this->getSubject($event))
        );
    }

    #[AsEventListener('workflow.entity_migration.completed')]
    public function flush(Event $event): void
    {
        $this->managerRegistry
            ->getManagerForClass(
                $this->getSubject($event)->getEntity()::class
            )->flush();
    }

    private function getSubject(GuardEvent|Event $event): EntityMigrationInterface
    {
        $subject = $event->getSubject();

        \assert($subject instanceof EntityMigrationInterface);

        return $subject;
    }

    private function getMigration(GuardEvent|Event $event): MigrationInterface
    {
        return $this->migrator->getMigration($this->getSubject($event)->getMigration()->getName());
    }
}
