<?php

namespace App\Sonata\Admin;

use App\Entity\UserMigration;
use Draw\Bundle\SonataIntegrationBundle\EntityMigrator\Admin\BaseEntityMigrationAdmin;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(
    name: 'sonata.admin',
    attributes: [
        ...BaseEntityMigrationAdmin::ADMIN,
        'model_class' => UserMigration::class,
        'label' => 'User Migration',
    ]
)]
class UserMigrationAdmin extends BaseEntityMigrationAdmin
{
}
