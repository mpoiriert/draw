<?php

namespace Draw\Bundle\SonataImportBundle\Column;

use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Entity\Import;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class ColumnFactory
{
    public function __construct(
        /**
         * @param iterable<ColumnExtractorInterface> $columnExtractors
         */
        #[TaggedIterator(ColumnExtractorInterface::class)]
        private iterable $columnExtractors = [],
    ) {
    }

    public function buildColumns(Import $import, array $headers, array $samples): void
    {
        $columns = [];
        foreach ($headers as $index => $headerName) {
            $column = (new Column())
                ->setImport($import)
                ->setIsDate(false)
                ->setHeaderName($headerName)
            ;

            $columnSamples = [];

            foreach ($samples as $rowSample) {
                $columnSample = $rowSample[$index];

                if (null === $column->getSample()) {
                    $column->setSample($columnSample);
                }

                if ($columnSample) {
                    $column->setSample($columnSample);
                }

                $columnSamples[] = $columnSample;
            }

            foreach ($this->columnExtractors as $columnBuilder) {
                $columnInfo = $columnBuilder->extractDefaultValue(clone $column, $columnSamples);
                if ($columnInfo) {
                    $this->assign($columnInfo, $column);
                }
            }

            if (null === $column->getIsIdentifier()) {
                $column->setIsIdentifier(false);
            }

            $column->setIsIgnored(!$column->getMappedTo());
            if ($column->getIsIdentifier()) {
                $column->setIsIgnored(false);
            }

            $columns[] = $column;
        }

        foreach ($columns as $column) {
            $import->addColumn($column);
        }
    }

    private function assign(Column $source, Column $target): void
    {
        if (null !== $source->getIsIdentifier()) {
            $target->setIsIdentifier($source->getIsIdentifier());
        }

        if (null !== $source->getIsDate()) {
            $target->setIsDate($source->getIsDate());
        }

        if (null !== $source->getMappedTo()) {
            $target->setMappedTo($source->getMappedTo());
        }
    }
}
