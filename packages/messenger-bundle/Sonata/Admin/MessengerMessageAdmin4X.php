<?php

namespace Draw\Bundle\MessengerBundle\Sonata\Admin;

use Sonata\AdminBundle\Route\RouteCollectionInterface;

class MessengerMessageAdmin4X extends MessengerMessageAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $this->backwardCompatibleConfigureRoute($collection);
    }
}
