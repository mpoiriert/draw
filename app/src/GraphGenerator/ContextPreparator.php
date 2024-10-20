<?php

namespace App\GraphGenerator;

use App\Entity\User;
use Draw\Bundle\SonataImportBundle\Entity\Import;
use Draw\DoctrineExtra\ORM\GraphSchema\Event\PrepareContextEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class ContextPreparator
{
    #[AsEventListener]
    public function prepareUser(PrepareContextEvent $event): void
    {
        $context = $event->getContext();

        if ('user' !== $context->getName()) {
            return;
        }

        $event->getContext()
            ->setIgnoreAll(true)
            ->forEntityCluster(User::class)
        ;
    }

    #[AsEventListener]
    public function prepareImport(PrepareContextEvent $event): void
    {
        $context = $event->getContext();

        if ('import' !== $context->getName()) {
            return;
        }

        $event->getContext()
            ->setIgnoreAll(true)
            ->forEntityCluster(Import::class)
        ;
    }
}
