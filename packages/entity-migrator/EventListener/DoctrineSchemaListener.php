<?php

namespace Draw\Component\EntityMigrator\EventListener;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Draw\Component\EntityMigrator\Entity\EntityMigrationInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(
    'doctrine.event_listener',
    [
        'event' => 'loadClassMetadata',
    ]
)]
class DoctrineSchemaListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $metadata = $eventArgs->getClassMetadata();

        if (!$metadata instanceof ClassMetadataInfo) {
            return;
        }

        if (!\in_array(EntityMigrationInterface::class, class_implements($metadata->getName()), true)) {
            return;
        }

        $metadata->table['indexes']['draw_migration_sate'] = [
            'columns' => ['migration_id', 'state'],
        ];
    }
}
