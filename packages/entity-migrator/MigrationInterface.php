<?php

namespace Draw\Component\EntityMigrator;

use Doctrine\ORM\QueryBuilder;

/**
 * @template T of MigrationTargetEntityInterface
 */
interface MigrationInterface
{
    public static function getName(): string;

    /**
     * @return class-string<T>
     */
    public static function getTargetEntityClass(): string;

    /**
     * @param T $entity
     */
    public function migrate(MigrationTargetEntityInterface $entity): void;

    /**
     * @param T $entity
     */
    public function needMigration(MigrationTargetEntityInterface $entity): bool;

    public function createSelectIdQueryBuilder(): QueryBuilder;
}
