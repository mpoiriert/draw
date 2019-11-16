<?php namespace Draw\Bundle\TesterBundle\DependencyInjection;

trait ServiceTesterTrait
{
    public function getService($service)
    {
        return static::bootKernel()->getContainer()->get('test.service_container')->get($service);
    }
}