<?php

namespace Draw\Bundle\SonataImportBundle\Column\MappedToOptionBuilder;

use Draw\Bundle\SonataImportBundle\Entity\Column;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(MappedToOptionBuilderInterface::class)]
interface MappedToOptionBuilderInterface
{
    /**
     * You must return the new options with the current one merged or modified.
     *
     * @return array<string, string> the options to be used for the mapped to field
     */
    public function getOptions(Column $column, array $options): array;
}
