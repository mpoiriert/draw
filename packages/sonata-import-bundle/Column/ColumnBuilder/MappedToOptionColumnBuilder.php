<?php

namespace Draw\Bundle\SonataImportBundle\Column\ColumnBuilder;

use Draw\Bundle\SonataImportBundle\Column\MappedToOptionBuilderAggregator;
use Draw\Bundle\SonataImportBundle\Entity\Column;

class MappedToOptionColumnBuilder implements ColumnBuilderInterface
{
    public static function getDefaultPriority(): int
    {
        return -100;
    }

    public function __construct(
        private MappedToOptionBuilderAggregator $mappedToOptionBuilderAggregator
    ) {
    }

    public function extract(Column $column, array $samples): ?Column
    {
        // We don't want to override the mappedTo value if it's already set
        if ($column->getMappedTo()) {
            return null;
        }

        if (\in_array($column->getHeaderName(), $this->mappedToOptionBuilderAggregator->getOptions($column))) {
            return (new Column())
                ->setMappedTo($column->getHeaderName());
        }

        return null;
    }
}
