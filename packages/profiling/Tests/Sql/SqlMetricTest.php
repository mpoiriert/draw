<?php

namespace Draw\Component\Profiling\Tests\Sql;

use Draw\Component\Profiling\Sql\SqlMetric;
use PHPUnit\Framework\TestCase;

class SqlMetricTest extends TestCase
{
    public function test(): void
    {
        $metric = new SqlMetric(['query']);
        static::assertEquals(1, $metric->count);
        static::assertEquals(
            ['query'],
            $metric->queries
        );
    }
}
