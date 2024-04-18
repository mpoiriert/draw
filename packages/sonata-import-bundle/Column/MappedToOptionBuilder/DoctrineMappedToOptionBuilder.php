<?php

namespace Draw\Bundle\SonataImportBundle\Column\MappedToOptionBuilder;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataImportBundle\Entity\Column;

class DoctrineMappedToOptionBuilder implements MappedToOptionBuilderInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function getOptions(Column $column, array $options): array
    {
        $class = $column->getImport()->getEntityClass();

        $metadata = $this->managerRegistry->getManagerForClass($class)->getClassMetadata($class);

        if (!$metadata instanceof ClassMetadata) {
            return $options;
        }

        $choices = [];
        foreach ($metadata->fieldMappings as $fieldMapping) {
            $choices[$fieldMapping['columnName']] = $fieldMapping['columnName'];
        }

        foreach ($metadata->associationMappings as $name => $associationMapping) {
            $targetClassMetadata = $this->managerRegistry
                ->getManagerForClass($associationMapping['targetEntity'])
                ->getClassMetadata($associationMapping['targetEntity']);

            if (!$targetClassMetadata instanceof ClassMetadata) {
                continue;
            }

            foreach ($targetClassMetadata->fieldMappings as $fieldName => $fieldMapping) {
                if (!($fieldMapping['id'] ?? false) && !($fieldMapping['unique'] ?? false)) {
                    continue;
                }

                $choices[$name.'.'.$fieldName] = $name.'.'.$fieldName;
            }
        }

        return $choices;
    }
}
