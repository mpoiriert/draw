<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Event\CleanEvent;
use Draw\Component\OpenApi\Extraction\Extractor\Doctrine\InheritanceExtractor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DoctrineInheritanceSchemaCleanerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CleanEvent::class => ['onClean', -1],
        ];
    }

    public function onClean(CleanEvent $event): void
    {
        $rootSchema = $event->getRootSchema();

        foreach ($rootSchema->definitions as $definition) {
            $definition->removeVendorDataKey(InheritanceExtractor::VENDOR_DATA_DOCTRINE_IS_ROOT_ENTITY);
            $definition->removeVendorDataKey(InheritanceExtractor::VENDOR_DATA_DOCTRINE_ROOT_ENTITY_CLASS);
            $definition->removeVendorDataKey(InheritanceExtractor::VENDOR_DATA_DOCTRINE_ENTITY_CLASS);
        }

        $event->setRootSchema($rootSchema);
    }
}
