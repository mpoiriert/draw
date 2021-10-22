<?php

namespace Draw\Bundle\UserBundle\Sonata\Twig;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Twig\Extension\RuntimeExtensionInterface;

class UserAdminRuntime implements RuntimeExtensionInterface
{
    private $userAdminCode;
    private $pool;

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
