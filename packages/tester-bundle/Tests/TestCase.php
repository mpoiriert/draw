<?php

namespace Draw\Bundle\TesterBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TestCase extends KernelTestCase
{
    // For symfony 4.x
    protected static $class = AppKernel::class;

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }
}
