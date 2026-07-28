<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;
use Symfony\Component\DependencyInjection\Container;
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

    /**
     * Returns the real (public) container, bypassing TestContainer.
     *
     * This is needed because TestContainer::set() routes services that exist
     * in the private services locator to $container->privates, but compiled
     * service factories resolve dependencies from $container->services.
     */
    private function getPublicContainer(TestCase $testCase): Container
    {
        $container = $this->getContainer($testCase);

        if ($container instanceof TestContainer) {
            $method = new \ReflectionMethod($container, 'getPublicContainer');

            return $method->invoke($container);
        }

        \assert($container instanceof Container);

        return $container;
    }
}
