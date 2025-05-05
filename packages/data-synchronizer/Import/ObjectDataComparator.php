<?php

namespace Draw\Component\DataSynchronizer\Import;

class ObjectDataComparator
{
    public function isSame(array $entityData1, array $entityData2): bool
    {
        return $this->sortProperty($entityData1) == $this->sortProperty($entityData2);
    }

    /**
     * Sort array by property since they might not be ordered properly causing a diff
     * which as no real impact: E.g. tags [{name: tag1}, {name: tag2}] should equal [{name: tag2}, {name: tag1}].
     */
    private function sortProperty(array $entityData): array
    {
        foreach ($entityData as $key => $value) {
            if (!\is_array($value) || 0 === \count($value) || !is_numeric(key($value))) {
                continue;
            }

            $row = $value[0];

            if (!\is_array($row)) {
                continue;
            }

            foreach (['code', 'name'] as $column) {
                if (\array_key_exists($column, $row)) {
                    $columnValues = array_column($value, $column);
                    array_multisort($columnValues, $value);

                    $entityData[$key] = $value;
                    break;
                }
            }
        }

        return $entityData;
    }
}
