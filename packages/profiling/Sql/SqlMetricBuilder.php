<?php

namespace Draw\Component\Profiling\Sql;

use Draw\Component\Profiling\MetricBuilderInterface;

class SqlMetricBuilder implements MetricBuilderInterface
{
    /**
     * @var SqlLog[]
     */
    private $logs = [];

    public function addLog(SqlLog $sqlLog): void
    {
        $this->logs[] = $sqlLog;
    }

    /**
     * @return SqlMetric
     */
    public function build()
    {
        $queries = array_map(
            fn (SqlLog $log) => $log->query,
            $this->logs
        );

        return new SqlMetric($queries);
    }
}
