<?php

namespace Draw\Bundle\SonataImportBundle\Column\ColumnBuilder\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataImportBundle\Column\ColumnBuilder\ColumnBuilderInterface;
use Draw\Bundle\SonataImportBundle\Entity\Column;

class DoctrineAssociationFieldColumnBuilder implements ColumnBuilderInterface
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

        $associationMapping = $metadata->associationMappings[$headerName] ?? null;

        if (!$associationMapping) {
            return null;
        }

        return (new Column())
            ->setMappedTo($headerName.'.'.$associationMapping['joinColumns'][0]['referencedColumnName'])
            ->setIsIdentifier(false)
            ->setIsDate(false);
    }
}
