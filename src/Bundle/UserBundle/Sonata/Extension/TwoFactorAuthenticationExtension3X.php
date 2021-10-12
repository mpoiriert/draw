<?php

namespace Draw\Bundle\UserBundle\Sonata\Extension;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;

class TwoFactorAuthenticationExtension3X extends TwoFactorAuthenticationExtension
{
    public function configureRoutes(AdminInterface $admin, RouteCollection $collection): void
    {
        parent::backwardCompatibleConfigureRoute($admin, $collection);
    }

    public function configureActionButtons(AdminInterface $admin, $list, $action, $object): array
    {
        return parent::backwardCompatibleConfigureActionButtons($admin, $list, $action, $object);
    }
}
