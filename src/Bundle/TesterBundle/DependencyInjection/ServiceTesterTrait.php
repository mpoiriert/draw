<?php

namespace Draw\Bundle\TesterBundle\DependencyInjection;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @method static KernelInterface bootKernel(array $options = [])
 */
trait ServiceTesterTrait
{
    private static $mainTestKernel = null;
    private static $mainTestContainer = null;

    public static function getService($service)
    {
        if (null === self::$mainTestContainer) {
            self::$mainTestKernel = static::createKernel();
            self::$mainTestKernel->boot();
            $container = self::$mainTestKernel->getContainer();
            self::$mainTestContainer = $container->has('test.service_container') ? $container->get('test.service_container') : $container;
        }

        return self::$mainTestContainer->get($service);
    }

    /**
     * @afterClass
     */
    public static function shutdownMainTestContainer()
    {
        if (null !== self::$mainTestKernel) {
            self::$mainTestKernel->shutdown();
        }

        if (self::$mainTestContainer instanceof ResetInterface) {
            self::$mainTestContainer->reset();
        }

        self::$mainTestKernel = self::$mainTestContainer = null;
    }
}
