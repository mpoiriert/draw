<?php

namespace Draw\Component\EntityMigrator;

use Doctrine\ORM\QueryBuilder;

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
     * Create a query builder to select all the entity to migrate.
     *
     * This query builder will be used to create a count query and a query to fetch all the entities or ids to migrate.
     */
    public function createQueryBuilder(): QueryBuilder;
}
