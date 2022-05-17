<?php

namespace Draw\Bundle\SonataExtraBundle\Controller;

use Sonata\AdminBundle\Admin\AdminInterface;

interface AdminControllerInterface
{
    public function configureAdmin(AdminInterface $admin): void;
}
