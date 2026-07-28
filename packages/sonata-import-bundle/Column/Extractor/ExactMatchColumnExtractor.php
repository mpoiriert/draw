<?php

namespace Draw\Bundle\SonataImportBundle\Column\Extractor;

use Draw\Bundle\SonataImportBundle\Column\BaseColumnExtractor;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Import\Importer;

class ExactMatchColumnExtractor extends BaseColumnExtractor
{
    public static function getDefaultPriority(): int
    {
        return -1000;
    }

    public function __construct(
        private Importer $importer,
    ) {
    }

    #[\Override]
    public function extractDefaultValue(Column $column, array $samples): ?Column
    {
        // We don't want to override the mappedTo value if it's already set
        if ($column->getMappedTo()) {
            return null;
        }

        if (\in_array($column->getHeaderName(), $this->importer->getOptions($column), true)) {
            return (new Column())
                ->setMappedTo($column->getHeaderName())
            ;
        }

        return null;
    }
}
