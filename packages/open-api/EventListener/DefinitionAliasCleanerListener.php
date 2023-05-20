<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Event\CleanEvent;
use Draw\Component\OpenApi\Schema\PathItem;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\Schema\Schema;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DefinitionAliasCleanerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CleanEvent::class => ['onClean', -50],
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
                    // If the replacement name is the same as the current index we do not do anything
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
}
