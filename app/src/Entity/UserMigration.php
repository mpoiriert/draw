<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Component\EntityMigrator\Entity\BaseEntityMigration;
use Draw\Component\EntityMigrator\MigrationTargetEntityInterface;

#[
    ORM\Entity,
    ORM\Table(name: 'user_migration'),
    ORM\UniqueConstraint(name: 'entity_migration', fields: ['entity', 'migration'])
]
class UserMigration extends BaseEntityMigration
{
    #[
        ORM\ManyToOne(targetEntity: User::class, cascade: ['refresh']),
        ORM\JoinColumn(name: 'entity_id', nullable: false, onDelete: 'CASCADE')
    ]
    protected MigrationTargetEntityInterface $entity;
}
