<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Event\CleanEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TagCleanerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CleanEvent::class => ['onClean', 1],
        ];
    }

    public function __construct(private array $tagsToClean = [])
    {
    }

    public function onClean(CleanEvent $event): void
    {
        $schema = $event->getRootSchema();

        if (!$schema->paths) {
            return;
        }

        foreach ($schema->paths as $path) {
            foreach ($path->getOperations() as $operation) {
                foreach ($operation->tags ?? [] as $index => $tag) {
                    if (\in_array($tag, $this->tagsToClean)) {
                        unset($operation->tags[$index]);
                    }
                }
            }
        }
    }
}
