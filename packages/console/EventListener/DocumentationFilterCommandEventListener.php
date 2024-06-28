<?php

namespace Draw\Component\Console\EventListener;

use Draw\Component\Console\Event\GenerateDocumentationEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class DocumentationFilterCommandEventListener
{
    public const FILTER_OUT = 'out';
    public const FILTER_IN = 'in';

    public function __construct(private array $commandNames, private string $filter = self::FILTER_OUT)
    {
    }

    #[AsEventListener]
    public function onGenerateDocumentationEvent(GenerateDocumentationEvent $event): void
    {
        $match = \in_array($event->getCommand()->getName(), $this->commandNames);

        if ($match && self::FILTER_OUT === $this->filter) {
            $event->ignore();

            return;
        }

        if (!$match && self::FILTER_IN === $this->filter) {
            $event->ignore();
        }
    }
}
