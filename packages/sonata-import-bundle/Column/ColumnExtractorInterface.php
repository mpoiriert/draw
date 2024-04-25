<?php

namespace Draw\Bundle\SonataImportBundle\Column;

use Draw\Bundle\SonataImportBundle\Entity\Column;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ColumnExtractorInterface::class)]
interface ColumnExtractorInterface
{
    public static function getDefaultPriority(): int;

    /**
     * You must return the new options with the current one merged or modified.
     *
     * @return array<string> the options to be used for the mapped to field
     */
    public function getOptions(Column $column, array $options): array;

    /**
     * Extract default value for the column. By returning a new column, you can override the current column value.
     *
     * If you don't want to override the current column value, return null.
     *
     * You should only handle columns that match your criteria.
     *
     * If you just want to assign the mappedTo value return null since it will be done automatically
     * if the header is the same as the mappedTo value.
     */
    public function extractDefaultValue(Column $column, array $samples): ?Column;

    /**
     * If the assignation is just calling a setter on the object, you can return false, it will be done automatically.
     *
     * @return bool True if the value was assigned, false otherwise
     */
    public function assign(object $object, Column $column, mixed $value): bool;
}
