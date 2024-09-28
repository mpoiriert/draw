<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AutowireLoggerTester extends AutowireService
{
    public function __construct()
    {
        parent::__construct('monolog.handler.testing');
    }
}
