<?php

namespace Draw\Bundle\SonataImportBundle\Import;

use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Entity\Import;

interface ImporterInterface
{
    public function getOptions(Column $column): array;

    public function buildFromFile(Import $import, \SplFileInfo $file): void;

    public function processImport(Import $import): bool;
}
