<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Event\CleanEvent;
use Draw\Component\OpenApi\Schema\PathItem;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\Schema\Schema;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DuplicateDefinitionAliasSchemaCleanerListener implements EventSubscriberInterface
{
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
