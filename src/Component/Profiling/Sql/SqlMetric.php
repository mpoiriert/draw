<?php

namespace Draw\Component\Profiling\Sql;

class SqlMetric
{
    /**
     * @var int
     */
    public $count = 0;

    /**
     * @var string[]
     */
    public $queries = [];

    public function __construct(array $queries)
    {
        $this->count = count($queries);
        $this->queries = $queries;
    }
}
