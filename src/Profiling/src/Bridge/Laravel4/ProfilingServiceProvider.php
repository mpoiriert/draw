<?php

namespace Draw\Profiling\Bridge\Laravel4;

use Draw\Profiling\MetricAggregatorProfiler;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ProfilingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('draw.profiler', function(Application $application) {
            $profiler = new MetricAggregatorProfiler();
            $profiler->registerProfiler($application[Laravel4SqlProfiler::class]);
            return $profiler;
        });

        $this->app->singleton(Laravel4SqlProfiler::class);
    }


    public function boot()
    {
        $this->app['draw.profiler']->start();
    }
}