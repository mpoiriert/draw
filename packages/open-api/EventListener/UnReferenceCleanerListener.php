<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Cleaner\ReferenceCleanerInterface;
use Draw\Component\OpenApi\Event\CleanEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UnReferenceCleanerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CleanEvent::class => 'onClean',
        ];
    }

    public function __construct(
        /**
         * @var iterable<ReferenceCleanerInterface>
         */
        private iterable $referenceCleaners = [],
    ) {
    }

    public function onClean(CleanEvent $event): void
    {
        $rootSchema = $event->getRootSchema();

        do {
            $cleaningOccurred = false;
            foreach ($this->referenceCleaners as $referenceCleaner) {
                if ($referenceCleaner->cleanReferences($rootSchema)) {
                    $cleaningOccurred = true;
                }
            }
        } while ($cleaningOccurred);

        $event->setRootSchema($rootSchema);
    }
}
