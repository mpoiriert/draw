<?php

namespace Draw\Bundle\AwsToolKitBundle\Tests;

use Draw\Bundle\TesterBundle\DependencyInjection\ServiceTesterTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TestCase extends KernelTestCase
{
    use ServiceTesterTrait;

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }
}
