<?php

namespace Draw\Profiling;

class MetricAggregatorProfiler implements ProfilerInterface
{
    /**
     * @var ProfilerInterface[]
     */
    private $profilers = [];

    private $started = false;

    public function isStarted()
    {
        return $this->started;
    }

    public function start()
    {
        $this->started = true;
        foreach($this->profilers as $profiler) {
            $profiler->start();
        }
    }

    /**
     * @return \stdClass
     */
    public function stop()
    {
        $this->started = false;
        $metrics = new \stdClass();

        foreach($this->profilers as $type => $profiler) {
            $metrics->{$type} = $profiler->stop();
        }

        return $metrics;
    }

    public function registerProfiler(ProfilerInterface $profiler)
    {
        $this->profilers[$profiler->getType()] = $profiler;
    }

    public function getType()
    {
        return 'aggregator';
    }
}