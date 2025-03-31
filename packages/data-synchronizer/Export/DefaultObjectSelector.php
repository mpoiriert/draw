<?php

namespace Draw\Component\DataSynchronizer\Export;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\DataSynchronizer\Metadata\EntitySynchronizationMetadata;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(ObjectSelectorInterface::class)]
class DefaultObjectSelector implements ObjectSelectorInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function select(EntitySynchronizationMetadata $extractionMetadata): ?array
    {
        $classMetadata = $extractionMetadata->classMetadata;

        $entityManager = $this->managerRegistry->getManagerForClass($classMetadata->name);

        \assert($entityManager instanceof EntityManagerInterface);

        $queryBuilder = $entityManager
            ->createQueryBuilder()
            ->select('entity')
            ->from($classMetadata->name, 'entity')
        ;

        if (ClassMetadataInfo::INHERITANCE_TYPE_NONE !== $classMetadata->inheritanceType) {
            if (!$classMetadata->discriminatorValue || $classMetadata->getReflectionClass()->isAbstract()) {
                return null;
            }

            $queryBuilder
                ->where('DISCRIMINATOR(entity) = :discriminator')
                ->setParameter('discriminator', $classMetadata->discriminatorValue)
            ;
        }

        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;
    }
}
