<?php namespace Draw\Component\Tester;

trait ServiceTesterTrait
{
    public function getService($service)
    {
        return static::bootKernel()->getContainer()->get($service);
    }
}