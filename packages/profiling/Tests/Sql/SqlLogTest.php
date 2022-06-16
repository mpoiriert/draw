<?php

namespace Draw\Component\Profiling\Tests\Sql;

use Draw\Component\Profiling\Sql\SqlLog;
use PHPUnit\Framework\TestCase;

class SqlLogTest extends TestCase
{
    public function test(): void
    {
        $log = new SqlLog('query');
        static::assertEquals('query', $log->query);
    }
}
