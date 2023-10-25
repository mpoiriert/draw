<?php

namespace Draw\Component\EntityMigrator;

use Draw\Component\EntityMigrator\Entity\BaseEntityMigration;

interface MigrationTargetEntityInterface
{
    /**
     * @return class-string<BaseEntityMigration>
     */
    public static function getEntityMigrationClass(): string;
}
