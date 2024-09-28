<?php

namespace App\Test\PHPUnit\Extension\SetUpAutowire;

use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireEntity;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AutowireAdminUser extends AutowireEntity
{
    public function __construct()
    {
        parent::__construct(['email' => 'admin@example.com']);
    }
}
