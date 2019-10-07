<?php namespace Draw\Component\Profiling\Sql;

use Draw\Component\Profiling\ProfilerInterface;

abstract class SqlProfiler implements ProfilerInterface
{
    const PROFILER_TYPE = 'sql';

    /**
     * @var SqlMetricBuilder
     */
    protected $metricBuilder;

    /**
     * @return SqlMetricBuilder
     */
    public function getMetricBuilder(): SqlMetricBuilder
    {
        if (is_null($this->metricBuilder)) {
            $this->metricBuilder = new SqlMetricBuilder();
        }

        return $this->metricBuilder;
    }

    /**
     * @return SqlMetric
     */
    public function stop()
    {
        return $this->getMetricBuilder()->build();
    }

    public function getType(): string
    {
        return self::PROFILER_TYPE;
    }
}