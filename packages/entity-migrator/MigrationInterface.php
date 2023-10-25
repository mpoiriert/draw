<?php

namespace Draw\Component\EntityMigrator;

/**
 * @template T of MigrationTargetEntityInterface
 */
interface MigrationInterface
{
    public static function getName(): string;

    public static function getTargetEntityClass(): string;

    /**
     * @param T $entity
     */
    public function migrate(MigrationTargetEntityInterface $entity): void;

    /**
     * @param T $entity
     */
    public function needMigration(MigrationTargetEntityInterface $entity): bool;

    /**
     * Return all entity that need migration. A migrate command will be sent to queue for each of them.
     *
     * @return iterable<T>
     */
    public function findAllThatNeedMigration(): iterable;

    /**
     * Return the number of entities that need migration or null if unknown.
     */
    public function countAllThatNeedMigration(): ?int;

    /**
     * Return a boolean to indicate that no more entities need migration.
     */
    public function migrationIsCompleted(): bool;
}
