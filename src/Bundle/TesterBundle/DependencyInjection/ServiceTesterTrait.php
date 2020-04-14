<?php namespace Draw\Bundle\TesterBundle\DependencyInjection;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @method static KernelInterface bootKernel(array $options = [])
 */
trait ServiceTesterTrait
{
    static function getService($service)
    {
        return static::bootKernel()->getContainer()->get('test.service_container')->get($service);
    }
}