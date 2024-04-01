<?php

namespace Draw\Bundle\SonataExtraBundle\EventListener;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\DoctrineORMAdminBundle\Event\PreObjectDeleteBatchEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class PreObjectDeleteBatchEventEventListener
{
    public function __construct(private Pool $pool)
    {
    }

    #[AsEventListener]
    public function handlePreObjectDeleteBatchEvent(PreObjectDeleteBatchEvent $event): void
    {
        $canDelete = $this->pool
            ->getAdminByClass($event->getClassName())
            ->hasAccess('delete', $event->getObject());

        if (!$canDelete) {
            $event->preventDelete();
        }
    }
}
