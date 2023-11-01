<?php

namespace Draw\Component\EntityMigrator;

use Doctrine\ORM\QueryBuilder;

/**
 * @template T of MigrationTargetEntityInterface
 *
 * @template-extends MigrationInterface<T>
 */
interface BatchPrepareMigrationInterface extends MigrationInterface
{
    public function createSelectIdQueryBuilder(): QueryBuilder;
}
