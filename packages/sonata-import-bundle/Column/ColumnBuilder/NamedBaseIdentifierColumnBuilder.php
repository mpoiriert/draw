<?php

namespace Draw\Bundle\SonataImportBundle\Column\ColumnBuilder;

use Draw\Bundle\SonataImportBundle\Entity\Column;

class NamedBaseIdentifierColumnBuilder implements ColumnBuilderInterface
{
    private static array $names = ['id'];

    public function extract(string $class, Column $column, array $samples): ?Column
    {
        $headerName = $column->getHeaderName();
        if (!\in_array(mb_strtolower($headerName), self::$names, true)) {
            return null;
        }

        return (new Column())
            ->setIsIdentifier(true);
    }
}
