<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
