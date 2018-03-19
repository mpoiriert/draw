<?php

namespace Draw\Profiling\Sql;

class SqlLog
{
    public $query;

    public function __construct($query)
    {
        $this->query = $query;
    }
}