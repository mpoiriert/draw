<?php

namespace Draw\Component\EntityMigrator;

use Draw\Component\EntityMigrator\Entity\BaseEntityMigration;

interface MigrationTargetEntityInterface extends \Stringable
{
    /**
     * @return class-string<BaseEntityMigration>
     */
    public static function getEntityMigrationClass(): string;
}
