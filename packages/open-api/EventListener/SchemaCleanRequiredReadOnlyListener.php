<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SchemaCleanRequiredReadOnlyListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PreDumpRootSchemaEvent::class => ['cleanReadOnly', 255],
        ];
    }

    public function cleanReadOnly(PreDumpRootSchemaEvent $event): void
    {
        $root = $event->getSchema();

        foreach ($root->definitions as $definition) {
            foreach ($definition->properties as $propertyNane => $property) {
                if (!$definition->required) {
                    continue;
                }

                if (!$property->readOnly) {
                    continue;
                }

                $definition->required = array_values(
                    array_filter(
                        $definition->required,
                        fn ($requiredPropertyName) => $requiredPropertyName !== $propertyNane
                    )
                );
            }
        }
    }
}
