<?php

namespace Draw\Bundle\MessengerBundle\Sonata\Admin;

use Sonata\AdminBundle\Route\RouteCollection;

class MessengerMessageAdmin3X extends MessengerMessageAdmin
{
    protected function configureRoutes(RouteCollection $collection): void
    {
        $this->backwardCompatibleConfigureRoute($collection);
    }
}
