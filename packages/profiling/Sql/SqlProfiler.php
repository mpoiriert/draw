<?php

namespace Draw\Component\Profiling\Sql;

use Draw\Component\Profiling\ProfilerInterface;

abstract class SqlProfiler implements ProfilerInterface
{
    final public const PROFILER_TYPE = 'sql';

    protected ?SqlMetricBuilder $metricBuilder = null;

    public function getMetricBuilder(): SqlMetricBuilder
    {
        if (null === $this->metricBuilder) {
            $this->metricBuilder = new SqlMetricBuilder();
        }

        return $this->metricBuilder;
    }

    public function stop(): SqlMetric
    {
        return $this->getMetricBuilder()->build();
    }

    public function getType(): string
    {
        return self::PROFILER_TYPE;
    }
}
