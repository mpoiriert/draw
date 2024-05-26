<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

use PHPUnit\Framework\TestCase;

interface AutowireInterface
{
    public static function getPriority(): int;

    public function autowire(TestCase $testCase, \ReflectionProperty $reflectionProperty);
}
