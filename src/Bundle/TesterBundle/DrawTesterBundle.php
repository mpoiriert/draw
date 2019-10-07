<?php namespace Draw\Bundle\TesterBundle;

use Draw\Component\Profiling\ProfilerCoordinator;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawTesterBundle extends Bundle
{
    public function boot()
    {
        if ($this->container->has(ProfilerCoordinator::class)) {
            $this->container->get(ProfilerCoordinator::class)->startAll();
        }
    }
}