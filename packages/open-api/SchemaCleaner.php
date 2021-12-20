<?php

namespace Draw\Component\OpenApi;

use Draw\Component\OpenApi\Schema\PathItem;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\Schema\Schema;

/**
 * This class is to clean up the schema before dumping it.
 * It will remove duplicate definition alias.
 *
 * @internal
 */
class SchemaCleaner
{
    public const VENDOR_DATA_KEEP = 'x-draw-open-api-keep';

    /**
     * @return Root The cleaned schema
     */
    public function clean(Root $rootSchema)
    {
        // This is to "clone" the object recursively
        /** @var Root $rootSchema */
        $rootSchema = unserialize(serialize($rootSchema));

        do {
            $definitionSchemasByObject = [];
            foreach ($rootSchema->definitions as $name => $definitionSchema) {
                $definitionSchemasByObject[parse_url($name)['path']][$name] = $definitionSchema;
            }

            $replaceSchemas = [];
            foreach ($definitionSchemasByObject as $definitionSchemas) {
                /** @var Schema[] $selectedSchemas */
                $selectedSchemas = [];
                array_walk($definitionSchemas,
                    function (Schema $schema, $name) use (&$selectedSchemas, &$replaceSchemas) {
                        foreach ($selectedSchemas as $selectedName => $selectedSchema) {
                            if ($this->isEqual($selectedSchema, $schema)) {
                                $replaceSchemas[$name] = $selectedName;

                                return;
                            }
                        }
                        $selectedSchemas[$name] = $schema;
                    });
            }

            foreach ($replaceSchemas as $toReplace => $replaceWith) {
                $this->replaceSchemaReference(
                    $rootSchema,
                    '#/definitions/'.$toReplace,
                    '#/definitions/'.$replaceWith
                );

                unset($rootSchema->definitions[$toReplace]);
            }
        } while (count($replaceSchemas));

        do {
            $suppressionOccurred = false;
            foreach ($rootSchema->definitions as $name => $definitionSchema) {
                if ($definitionSchema->getVendorData()[static::VENDOR_DATA_KEEP] ?? false) {
                    continue;
                }
                if (!$this->hasSchemaReference($rootSchema, '#/definitions/'.$name)) {
                    unset($rootSchema->definitions[$name]);
                    $suppressionOccurred = true;
                }
            }
        } while ($suppressionOccurred);

        // Rename aliases in case of skip to be cleaner (e.g.: [User?3, User?6] => [User, User?1])
        $definitionsToRename = [];
        foreach ($rootSchema->definitions as $name => $definitionSchema) {
            $definitionsToRename[parse_url($name)['path']][] = $name;
        }

        foreach ($definitionsToRename as $objectName => $names) {
            array_walk($names,
                function ($name, $index) use ($objectName, $rootSchema) {
                    $replaceWith = $objectName.($index ? '?'.$index : '');
                    // If the replace name is the same as the current index we do not do anything
                    if ($replaceWith == $name) {
                        return;
                    }
                    $rootSchema->definitions[$replaceWith] = $rootSchema->definitions[$name];
                    unset($rootSchema->definitions[$name]);
                    $this->replaceSchemaReference(
                        $rootSchema,
                        '#/definitions/'.$name,
                        '#/definitions/'.$replaceWith
                    );
                });
        }

        return $rootSchema;
    }

    private function hasSchemaReference($data, $reference)
    {
        if (!is_object($data) && !is_array($data)) {
            return false;
        }

        if (is_object($data)) {
            if ($data instanceof Schema || $data instanceof PathItem) {
                if ($data->ref == $reference) {
                    return true;
                }
            }
        }

        foreach ($data as &$value) {
            if ($this->hasSchemaReference($value, $reference)) {
                return true;
            }
        }

        return false;
    }

    private function replaceSchemaReference($data, $definitionToReplace, $definitionToReplaceWith)
    {
        if (!is_object($data) && !is_array($data)) {
            return;
        }

        if (is_object($data)) {
            if ($data instanceof Schema || $data instanceof PathItem) {
                if ($data->ref == $definitionToReplace) {
                    $data->ref = $definitionToReplaceWith;
                }
            }
        }

        foreach ($data as &$value) {
            $this->replaceSchemaReference($value, $definitionToReplace, $definitionToReplaceWith);
        }
    }

    /**
     * @return bool
     */
    private function isEqual(Schema $schemaA, Schema $schemaB)
    {
        return $schemaA == $schemaB;
    }
}
