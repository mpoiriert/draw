<?php

namespace Draw\Component\Profiling\Tests\Sql;

use Draw\Component\Profiling\Sql\SqlLog;
use Draw\Component\Profiling\Sql\SqlMetricBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class SqlMetricBuilderTest extends TestCase
{
    private SqlMetricBuilder $metricBuilder;

    protected function setUp(): void
    {
        $this->metricBuilder = new SqlMetricBuilder();
    }

    public function testBuild(): void
    {
        $this->metricBuilder->addLog(new SqlLog('query'));
        $metric = $this->metricBuilder->build();

        $this->assertSame(1, $metric->count);
        $this->assertSame(
            ['query'],
            $metric->queries
        );
    }
}
