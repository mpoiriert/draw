<?php

namespace Draw\Bundle\CommandBundle\Sonata\Admin;

use Sonata\AdminBundle\Route\RouteCollection;

class ExecutionAdmin3X extends ExecutionAdmin
{
    protected function configureRoutes(RouteCollection $collection): void
    {
        $this->backwardCompatibleConfigureRoute($collection);
    }

    public function configureActionButtons($action, $object = null): array
    {
        return $this->backwardCompatibleConfigureActionButtons(
            parent::configureActionButtons($action, $object), $action, $object
        );
    }
}
