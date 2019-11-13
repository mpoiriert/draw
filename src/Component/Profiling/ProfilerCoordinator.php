<?php namespace Draw\Component\Profiling;

class ProfilerCoordinator
{
    /**
     * @var ProfilerInterface[]
     */
    private $profilers = [];

    private $started = false;

    public function __construct(iterable $profilers = [])
    {
        foreach ($profilers as $profiler) {
            $this->registerProfiler($profiler);
        }
    }

    public function isStarted(): bool
    {
        return $this->started;
    }

    public function startAll(): void
    {
        $this->started = true;
        foreach ($this->profilers as $profiler) {
            $profiler->start();
        }
    }

    /**
     * @return \stdClass
     */
    public function stopAll()
    {
        $this->started = false;
        $metrics = new \stdClass();

        foreach ($this->profilers as $type => $profiler) {
            $metrics->{$type} = $profiler->stop();
        }

        return $metrics;
    }

    public function registerProfiler(ProfilerInterface $profiler): void
    {
        $this->profilers[$profiler->getType()] = $profiler;
    }
}