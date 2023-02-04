<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Twig;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Twig\Extension\RuntimeExtensionInterface;

class UserAdminRuntime implements RuntimeExtensionInterface
{
    public function __construct(private Pool $pool, private string $userAdminCode)
    {
    }

    public function getUserAdmin(): AdminInterface
    {
        return $this->pool->getAdminByAdminCode($this->userAdminCode);
    }
}
