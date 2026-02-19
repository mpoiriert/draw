<?php

namespace Draw\Component\DataSynchronizer;

use Draw\Component\DataSynchronizer\Export\DataExporter;
use Draw\Component\DataSynchronizer\Import\DataImporter;

class DataSynchronizer
{
    public function __construct(
        private DataImporter $dataImporter,
        private DataExporter $dataExporter,
    ) {
    }

    public function export(): string
    {
        return $this->dataExporter->export();
    }

    public function import(string $file): void
    {
        $this->dataImporter->import($file);
    }
}
