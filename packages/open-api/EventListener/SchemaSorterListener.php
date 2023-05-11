<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SchemaSorterListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PreDumpRootSchemaEvent::class => ['sortSchema', -255],
        ];
    }

    public function sortSchema(PreDumpRootSchemaEvent $event): void
    {
        $root = $event->getSchema();

        ksort($root->paths);
        ksort($root->definitions);
        foreach ($root->definitions as $definition) {
            if ($definition->properties) {
                ksort($definition->properties);
            }
        }
    }
}
