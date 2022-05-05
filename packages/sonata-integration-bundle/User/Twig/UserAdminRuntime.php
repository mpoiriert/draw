<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Twig;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Twig\Extension\RuntimeExtensionInterface;

class UserAdminRuntime implements RuntimeExtensionInterface
{
    private string $userAdminCode;
    private Pool $pool;

    public function __construct(Pool $pool, string $userAdminCode)
    {
        $this->pool = $pool;
        $this->userAdminCode = $userAdminCode;
    }

    public function getUserAdmin(): AdminInterface
    {
        return $this->pool->getAdminByAdminCode($this->userAdminCode);
    }
}
