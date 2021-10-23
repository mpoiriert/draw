<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\Mock;

use Sonata\AdminBundle\Admin\AbstractAdmin;

class Admin extends AbstractAdmin
{
    public function __construct($code, $class = Entity::class, $baseControllerName = null)
    {
        parent::__construct($code, $class, $baseControllerName);
    }
}
