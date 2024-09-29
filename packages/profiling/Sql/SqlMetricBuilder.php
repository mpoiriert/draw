<?php

namespace Draw\Component\Profiling\Sql;

use Draw\Component\Profiling\MetricBuilderInterface;

class SqlMetricBuilder implements MetricBuilderInterface
{
    /**
     * @var SqlLog[]
     */
    private array $logs = [];

    public function addLog(SqlLog $sqlLog): void
    {
        $this->logs[] = $sqlLog;
    }

    public function build(): SqlMetric
    {
        $queries = array_map(
            static fn (SqlLog $log) => $log->query,
            $this->logs
        );

        return new SqlMetric($queries);
    }
}
