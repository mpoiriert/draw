<?php

namespace Draw\Bundle\SonataImportBundle\Column\ColumnBuilder\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataImportBundle\Column\ColumnBuilder\ColumnBuilderInterface;
use Draw\Bundle\SonataImportBundle\Entity\Column;

class DoctrineAssociationPathColumnBuilder implements ColumnBuilderInterface
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    public function extract(Column $column, array $samples): ?Column
    {
        $class = $column->getImport()->getEntityClass();
        $headerName = $column->getHeaderName();
        $manager = $this->managerRegistry->getManagerForClass($class);
        $metadata = $manager->getClassMetadata($class);

        if (!$metadata instanceof ClassMetadata) {
            return null;
        }

        if (1 !== substr_count($headerName, '.')) {
            return null;
        }

        [$relation, $fieldName] = explode('.', $headerName);

        $associationMapping = $metadata->associationMappings[$relation] ?? null;

        if (!$associationMapping) {
            return null;
        }

        $targetClass = $associationMapping['targetEntity'];

        $fieldMapping = $this->getFieldMapping($targetClass, $fieldName);

        if (!$fieldMapping) {
            return null;
        }

        return (new Column())
            ->setMappedTo($relation.'.'.$fieldName)
            ->setIsIdentifier($fieldMapping['id'] ?? false)
            ->setIsDate(str_starts_with($fieldMapping['type'], 'date'));
    }

    private function getFieldMapping(string $class, string $name): ?array
    {
        $manager = $this->managerRegistry->getManagerForClass($class);
        $metadata = $manager->getClassMetadata($class);

        if (!$metadata instanceof ClassMetadata) {
            return null;
        }

        return $metadata->fieldMappings[$name] ?? null;
    }
}
