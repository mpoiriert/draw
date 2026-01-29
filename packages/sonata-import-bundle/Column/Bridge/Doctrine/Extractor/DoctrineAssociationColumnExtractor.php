<?php

namespace Draw\Bundle\SonataImportBundle\Column\Bridge\Doctrine\Extractor;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataImportBundle\Column\BaseColumnExtractor;
use Draw\Bundle\SonataImportBundle\Entity\Column;

class DoctrineAssociationColumnExtractor extends BaseColumnExtractor
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[\Override]
    public function getOptions(Column $column, array $options): array
    {
        $class = $column->getImport()->getEntityClass();

        $metadata = $this->managerRegistry->getManagerForClass($class)->getClassMetadata($class);

        if (!$metadata instanceof ClassMetadata) {
            return $options;
        }

        foreach ($metadata->associationMappings as $name => $associationMapping) {
            if (!($associationMapping->type() & ClassMetadata::TO_ONE)) {
                continue;
            }

            $targetClassMetadata = $this->managerRegistry
                ->getManagerForClass($associationMapping->targetEntity)
                ->getClassMetadata($associationMapping->targetEntity)
            ;

            if (!$targetClassMetadata instanceof ClassMetadata) {
                continue;
            }

            foreach ($targetClassMetadata->fieldMappings as $fieldName => $fieldMapping) {
                if (!$targetClassMetadata->isIdentifier($fieldName) && !($fieldMapping->unique ?? false)) {
                    continue;
                }

                $options[] = $name.'.'.$fieldName;
            }
        }

        return $options;
    }

    #[\Override]
    public function assign(object $object, Column $column, mixed $value): bool
    {
        if (!\in_array($column->getMappedTo(), $this->getOptions($column, []), true)) {
            return false;
        }

        $class = $column->getImport()->getEntityClass();

        $classMetadata = $this->managerRegistry
            ->getManagerForClass($class)
            ->getClassMetadata($class)
        ;

        \assert($classMetadata instanceof ClassMetadata);

        [$relation, $field] = explode('.', $column->getMappedTo());

        $targetEntityClass = $classMetadata->associationMappings[$relation]->targetEntity;

        $targetEntity = $this->managerRegistry
            ->getRepository($targetEntityClass)
            ->findOneBy([$field => $value])
        ;

        if (null === $targetEntity) {
            return false;
        }

        $object->{'set'.ucfirst($relation)}($targetEntity);

        return true;
    }
}
