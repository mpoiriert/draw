<?php

namespace Draw\Bundle\SonataImportBundle\Column;

use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Exception\EntityClassNotSetException;

abstract class BaseColumnExtractor implements ColumnExtractorInterface
{
    public static function getDefaultPriority(): int
    {
        return 0;
    }

    public function getOptions(Column $column, array $options): array
    {
        return $options;
    }

    public function extractDefaultValue(Column $column, array $samples): ?Column
    {
        return null;
    }

    public function assign(object $object, Column $column, mixed $value): bool
    {
        return false;
    }

    protected function getEntityClass(Column $column): string
    {
        if (null === $class = $column->getImport()?->getEntityClass()) {
            throw new EntityClassNotSetException($column);
        }

        return $class;
    }
}
