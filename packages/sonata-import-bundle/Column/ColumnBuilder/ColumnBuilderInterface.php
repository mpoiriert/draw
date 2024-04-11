<?php

namespace Draw\Bundle\SonataImportBundle\Column\ColumnBuilder;

use Draw\Bundle\SonataImportBundle\Entity\Column;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ColumnBuilderInterface::class)]
interface ColumnBuilderInterface
{
    public function extract(string $class, Column $column, array $samples): ?Column;
}
