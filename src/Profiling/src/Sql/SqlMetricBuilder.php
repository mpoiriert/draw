<?php

namespace Draw\Profiling\Sql;

class SqlMetricBuilder
{
    /**
     * @var SqlLog[]
     */
    private $logs = [];

    public function addLog(SqlLog $sqlLog)
    {
        $this->logs[] = $sqlLog;
    }

    /**
     * @return SqlMetric
     */
    public function build()
    {
        $metric = new SqlMetric(count($this->logs), []);

        foreach($this->logs as $log)
        {
            $metric->queries[] = $log->query;
        }

        return $metric;
    }
}