<?php

namespace Draw\Bundle\FrameworkExtraBundle\Console\EventListener;

use Draw\Component\Console\Event\GenerateDocumentationEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class DocumentationIgnoredCommandEventListener
{
    public function __construct(private array $ignoredCommandNames)
    {
    }

    #[AsEventListener]
    public function onGenerateDocumentationEvent(GenerateDocumentationEvent $event): void
    {
        if (\in_array($event->getCommand()->getName(), $this->ignoredCommandNames)) {
            $event->ignore();
        }
    }
}
