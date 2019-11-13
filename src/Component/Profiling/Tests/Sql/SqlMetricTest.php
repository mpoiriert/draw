<?php namespace Draw\Component\Profiling\Tests\Sql;

use Draw\Component\Profiling\Sql\SqlMetric;
use PHPUnit\Framework\TestCase;

class SqlMetricTest extends TestCase
{
    public function test(): void
    {
        $metric = new SqlMetric(['query']);
        $this->assertEquals(1, $metric->count);
        $this->assertEquals(
            ['query'],
            $metric->queries
        );
    }
}