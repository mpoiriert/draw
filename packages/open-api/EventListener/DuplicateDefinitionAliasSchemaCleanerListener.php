<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Event\CleanEvent;
use Draw\Component\OpenApi\Schema\PathItem;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\Schema\Schema;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This class is to clean up the schema before dumping it.
 * It will remove duplicate definition alias.
 *
 * @internal
 */
class DuplicateDefinitionAliasSchemaCleanerListener implements EventSubscriberInterface
{
    final public const VENDOR_DATA_KEEP = 'x-draw-open-api-keep';

    public static function getSubscribedEvents(): array
    {
        return [
            CleanEvent::class => 'onClean',
        ];
    }

    public function onClean(CleanEvent $event): void
    {
        $event->setRootSchema(
            $this->clean($event->getRootSchema())
        );
    }

    /**
     * @return Root The cleaned schema
     */
    private function clean(Root $rootSchema): Root
    {
        do {
            $definitionSchemasByObject = [];
            foreach ($rootSchema->definitions as $name => $definitionSchema) {
                $definitionSchemasByObject[parse_url($name)['path']][$name] = $definitionSchema;
            }

            $replaceSchemas = [];
            foreach ($definitionSchemasByObject as $definitionSchemas) {
                /** @var Schema[] $selectedSchemas */
                $selectedSchemas = [];
                array_walk(
                    $definitionSchemas,
                    function (Schema $schema, $name) use (&$selectedSchemas, &$replaceSchemas): void {
                        foreach ($selectedSchemas as $selectedName => $selectedSchema) {
                            if ($this->isEqual($selectedSchema, $schema)) {
                                $replaceSchemas[$name] = $selectedName;

                                return;
                            }
                        }
                        $selectedSchemas[$name] = $schema;
                    }
                );
            }

            foreach ($replaceSchemas as $toReplace => $replaceWith) {
                $this->replaceSchemaReference(
                    $rootSchema,
                    '#/definitions/'.$toReplace,
                    '#/definitions/'.$replaceWith
                );

                unset($rootSchema->definitions[$toReplace]);
            }
        } while (\count($replaceSchemas));

        do {
            $suppressionOccurred = false;
            foreach ($rootSchema->definitions as $name => $definitionSchema) {
                if ($definitionSchema->getVendorDataKey(static::VENDOR_DATA_KEEP)) {
                    continue;
                }
                if (!$rootSchema->hasSchemaReference($rootSchema, '#/definitions/'.$name)) {
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
            array_walk(
                $names,
                function ($name, $index) use ($objectName, $rootSchema): void {
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
                }
            );
        }

        return $rootSchema;
    }

    private function replaceSchemaReference(
        mixed $data,
        string $definitionToReplace,
        string $definitionToReplaceWith
    ): void {
        if (!\is_object($data) && !\is_array($data)) {
            return;
        }

        if (!\is_array($data)) {
            if ($data instanceof Schema || $data instanceof PathItem) {
                if ($data->ref == $definitionToReplace) {
                    $data->ref = $definitionToReplaceWith;
                }
            }
        }

        foreach ($data as $value) {
            $this->replaceSchemaReference($value, $definitionToReplace, $definitionToReplaceWith);
        }
    }

    private function isEqual(Schema $schemaA, Schema $schemaB): bool
    {
        return $schemaA == $schemaB;
    }
}
