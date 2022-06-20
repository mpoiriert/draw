<?php

namespace Draw\Bundle\TesterBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @method static KernelInterface bootKernel(array $options = [])
 */
trait ServiceTesterTrait
{
    protected static $mainTestKernel = null;
    protected static $mainTestContainer = null;

    public static function getMainTestContainer(): Container
    {
        if (null === self::$mainTestContainer) {
            self::$mainTestKernel = static::createKernel();
            self::$mainTestKernel->boot();
            $container = self::$mainTestKernel->getContainer();
            self::$mainTestContainer = $container->get('test.service_container');
        }

        return self::$mainTestContainer;
    }

    public static function getService(string $service): ?object
    {
        return static::getMainTestContainer()->get($service);
    }

    /**
     * @afterClass
     */
    public static function shutdownMainTestContainer(): void
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
