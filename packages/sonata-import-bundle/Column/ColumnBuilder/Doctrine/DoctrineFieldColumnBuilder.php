<?php

namespace Draw\Bundle\SonataImportBundle\Column\ColumnBuilder\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataImportBundle\Column\ColumnBuilder\ColumnBuilderInterface;
use Draw\Bundle\SonataImportBundle\Entity\Column;

class DoctrineFieldColumnBuilder implements ColumnBuilderInterface
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    public function extract(string $class, Column $column, array $samples): ?Column
    {
        $headerName = $column->getHeaderName();
        $manager = $this->managerRegistry->getManagerForClass($class);
        $metadata = $manager->getClassMetadata($class);

        if (!$metadata instanceof ClassMetadata) {
            return null;
        }

        $fieldMapping = $metadata->fieldMappings[$headerName] ?? null;

        if (!$fieldMapping) {
            return null;
        }

        $columnInfo = (new Column())
            ->setMappedTo($headerName)
            ->setIsDate(str_starts_with($fieldMapping['type'], 'date'));

        if ($fieldMapping['id'] ?? false) {
            $columnInfo->setIsIdentifier(true);
        }

        if (null === $column->getIsIdentifier() && ($fieldMapping['unique'] ?? false)) {
            $columnInfo->setIsIdentifier(true);
        }

        return $columnInfo;

    }
}
