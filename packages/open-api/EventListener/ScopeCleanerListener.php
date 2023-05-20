<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Event\CleanEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ScopeCleanerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CleanEvent::class => ['onClean', 1000],
        ];
    }

    public function onClean(CleanEvent $cleanEvent): void
    {
        $rootSchema = $cleanEvent->getRootSchema();
        $extractionContext = $cleanEvent->getExtractionContext();

        if (!$rootSchema->paths) {
            return;
        }

        foreach ($rootSchema->paths as $path) {
            foreach ($path->getOperations() as $method => $operation) {
                if ($extractionContext->getOpenApi()->matchScope($extractionContext, $operation)) {
                    continue;
                }

                unset($path->{$method});
            }
        }

        $cleanEvent->setRootSchema($rootSchema);
    }
}
