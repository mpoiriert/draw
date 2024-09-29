<?php

namespace Draw\Component\Profiling\Tests\Sql;

use Draw\Component\Profiling\Sql\SqlMetric;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class SqlMetricTest extends TestCase
{
    public function test(): void
    {
        $metric = new SqlMetric(['query']);
        static::assertSame(1, $metric->count);
        static::assertSame(
            ['query'],
            $metric->queries
        );
    }
}
