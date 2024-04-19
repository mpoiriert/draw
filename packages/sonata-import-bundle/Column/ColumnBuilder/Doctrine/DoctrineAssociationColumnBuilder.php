<?php

namespace Draw\Bundle\SonataImportBundle\Column\ColumnBuilder\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataImportBundle\Column\ColumnBuilder\ColumnBuilderInterface;
use Draw\Bundle\SonataImportBundle\Entity\Column;

class DoctrineAssociationColumnBuilder implements ColumnBuilderInterface
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

        foreach ($metadata->associationMappings as $associationName => $associationMapping) {
            if (!isset($associationMapping['joinColumns'][0]['name'])) {
                continue;
            }

            $columnName = $associationMapping['joinColumns'][0]['name'];
            if ($columnName === $headerName) {
                return (new Column())
                    ->setMappedTo($associationName.'.'.$headerName)
                    ->setIsIdentifier(false)
                    ->setIsDate(false);
            }
        }

        return null;
    }
}
