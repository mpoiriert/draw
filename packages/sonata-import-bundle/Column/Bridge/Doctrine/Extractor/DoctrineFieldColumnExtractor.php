<?php

namespace Draw\Bundle\SonataImportBundle\Column\Bridge\Doctrine\Extractor;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataImportBundle\Column\BaseColumnExtractor;
use Draw\Bundle\SonataImportBundle\Entity\Column;

class DoctrineFieldColumnExtractor extends BaseColumnExtractor
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

        return array_merge(
            $options,
            array_values($metadata->fieldNames)
        );
    }

    #[\Override]
    public function extractDefaultValue(Column $column, array $samples): ?Column
    {
        if (!\in_array($column->getHeaderName(), $this->getOptions($column, []), true)) {
            return null;
        }

        $class = $column->getImport()->getEntityClass();
        $headerName = $column->getHeaderName();

        $classMetadata = $this->managerRegistry
            ->getManagerForClass($class)
            ->getClassMetadata($class)
        ;

        \assert($classMetadata instanceof ClassMetadata);

        $fieldMapping = $classMetadata->fieldMappings[$headerName];

        $columnInfo = (new Column())
            ->setMappedTo($headerName)
            ->setIsDate(str_starts_with($fieldMapping->type, 'date'))
        ;

        if ($classMetadata->isIdentifier($headerName)) {
            $columnInfo->setIsIdentifier(true);
        }

        if (null === $column->getIsIdentifier() && ($fieldMapping->unique ?? false)) {
            $columnInfo->setIsIdentifier(true);
        }

        return $columnInfo;
    }
}
