<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Event\CleanEvent;
use Draw\Component\OpenApi\Extraction\Extractor\Doctrine\InheritanceExtractor;
use Draw\Component\OpenApi\Schema\Root;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DoctrineInheritanceSchemaCleanerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CleanEvent::class => ['onClean', 1],
        ];
    }

    public function onClean(CleanEvent $event): void
    {
        $rootSchema = $event->getRootSchema();

        $this->removeUnReferenceRootEntity($rootSchema);

        foreach ($rootSchema->definitions as $name => $definitionSchema) {
            $rootEntityClass = $definitionSchema->getVendorDataKey(InheritanceExtractor::VENDOR_DATA_DOCTRINE_ROOT_ENTITY_CLASS);

            if (!$rootEntityClass) {
                continue;
            }

            if ($this->hasDoctrineClass($rootSchema, $rootEntityClass)) {
                $definitionSchema->setVendorDataKey(DuplicateDefinitionAliasSchemaCleanerListener::VENDOR_DATA_KEEP, true);
            } else {
                unset($rootSchema->definitions[$name]);
            }
        }

        foreach ($rootSchema->definitions as $definition) {
            $definition->removeVendorDataKey(InheritanceExtractor::VENDOR_DATA_DOCTRINE_IS_ROOT_ENTITY);
            $definition->removeVendorDataKey(InheritanceExtractor::VENDOR_DATA_DOCTRINE_ROOT_ENTITY_CLASS);
            $definition->removeVendorDataKey(InheritanceExtractor::VENDOR_DATA_DOCTRINE_ENTITY_CLASS);
        }

        $event->setRootSchema($rootSchema);
    }

    private function removeUnReferenceRootEntity(Root $rootSchema): void
    {
        do {
            $suppressionOccurred = false;
            foreach ($rootSchema->definitions as $name => $definitionSchema) {
                if (!$definitionSchema->getVendorDataKey(InheritanceExtractor::VENDOR_DATA_DOCTRINE_IS_ROOT_ENTITY)) {
                    continue;
                }

                if (!$rootSchema->hasSchemaReference($rootSchema, '#/definitions/'.$name)) {
                    unset($rootSchema->definitions[$name]);
                    $suppressionOccurred = true;
                }
            }
        } while ($suppressionOccurred);
    }

    public function hasDoctrineClass(Root $schema, string $className): bool
    {
        foreach ($schema->definitions as $definition) {
            $class = $definition->getVendorDataKey(InheritanceExtractor::VENDOR_DATA_DOCTRINE_ENTITY_CLASS);

            if ($class === $className) {
                return true;
            }
        }

        return false;
    }
}
