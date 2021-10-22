<?php

namespace Draw\Component\Profiling\Sql;

class SqlLog
{
    public $query;

    public function __construct(string $query)
    {
        $this->query = $query;
    }
}
