<?php

namespace Draw\Bundle\UserBundle\Sonata\Extension;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class TwoFactorAuthenticationExtension4X extends TwoFactorAuthenticationExtension
{
    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
        $this->backwardCompatibleConfigureRoute($admin, $collection);
    }

    public function configureActionButtons(AdminInterface $admin, $list, $action, ?object $object = null): array
    {
        return parent::backwardCompatibleConfigureActionButtons($admin, $list, $action, $object);
    }
}
