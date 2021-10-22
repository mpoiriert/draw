<?php

namespace Draw\Bundle\CommandBundle\Sonata\Admin;

use Sonata\AdminBundle\Route\RouteCollectionInterface;

class ExecutionAdmin4X extends ExecutionAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $this->backwardCompatibleConfigureRoute($collection);
    }

    public function configureActionButtons(array $buttonList, $action, $object = null): array
    {
        return $this->backwardCompatibleConfigureActionButtons($buttonList, $action, $object);
    }
}
