<?php

namespace Draw\Component\EntityMigrator\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\Entity\EntityMigrationInterface;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\EntityMigrator\MigrationTargetEntityInterface;

class EntityMigrationRepository
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function load(MigrationTargetEntityInterface $entity, Migration $migration): EntityMigrationInterface
    {
        $entityMigrationClass = $entity::getEntityMigrationClass();

        $entityMigration = $this->managerRegistry->getRepository($entityMigrationClass)
            ->findOneBy(['entity' => $entity, 'migration' => $migration])
        ;

        if (null === $entityMigration) {
            $entityMigration = new $entityMigrationClass($entity, $migration);
            $manager = $this->managerRegistry->getManagerForClass($entityMigrationClass);
            $manager->persist($entityMigration);
            $manager->flush();
        }

        return $entityMigration;
    }
}
