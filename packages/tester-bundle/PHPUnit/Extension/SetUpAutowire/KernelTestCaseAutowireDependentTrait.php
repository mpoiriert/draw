<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

trait KernelTestCaseAutowireDependentTrait
{
    private function getContainer(TestCase $testCase): ContainerInterface
    {
        \assert($testCase instanceof KernelTestCase);

        $container = (new \ReflectionMethod($testCase, 'getContainer'))->invoke($testCase);

        \assert($container instanceof ContainerInterface);

        return $container;
    }
}
