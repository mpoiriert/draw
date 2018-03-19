<?php

namespace Draw\Profiling\Sql;

use Draw\Profiling\ProfilerInterface;

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
    public function getMetricBuilder()
    {
        if(is_null($this->metricBuilder)) {
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

    public function getType()
    {
        return self::PROFILER_TYPE;
    }

}