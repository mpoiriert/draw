<?php

namespace Draw\Profiling\Sql;

class SqlMetric
{
    /**
     * @var integer
     */
    public $count = 0;

    /**
     * @var string[]
     */
    public $queries = [];

    public function __construct($count = 0, array $queries = [])
    {
        $this->count = $count;
        $this->queries = $queries;
    }
}