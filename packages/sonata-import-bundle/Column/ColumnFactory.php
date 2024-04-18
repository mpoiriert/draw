<?php

namespace Draw\Bundle\SonataImportBundle\Column;

use Draw\Bundle\SonataImportBundle\Column\ColumnBuilder\ColumnBuilderInterface;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class ColumnFactory
{
    public function __construct(
        /**
         * @param iterable<ColumnBuilderInterface> $columnBuilders
         */
        #[TaggedIterator(ColumnBuilderInterface::class)]
        private iterable $columnBuilders = []
    ) {
    }

    /**
     * @return Column[]
     */
    public function generateColumns(string $class, array $headers, array $samples): array
    {
        $columns = [];
        foreach ($headers as $index => $headerName) {
            $column = (new Column())
                ->setIsDate(false)
                ->setHeaderName($headerName);

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

            foreach ($this->columnBuilders as $extractor) {
                $columnInfo = $extractor->extract($class, clone $column, $columnSamples);
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

        return $columns;
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
