<?php

namespace Draw\Component\Profiling\Tests\Sql;

use Draw\Component\Profiling\MetricBuilderInterface;
use Draw\Component\Profiling\Sql\SqlLog;
use Draw\Component\Profiling\Sql\SqlMetric;
use Draw\Component\Profiling\Sql\SqlMetricBuilder;
use PHPUnit\Framework\TestCase;

class SqlMetricBuilderTest extends TestCase
{
    private $metricBuilder;

    protected function setUp(): void
    {
        $this->metricBuilder = new SqlMetricBuilder();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(MetricBuilderInterface::class, $this->metricBuilder);
    }

    public function testBuild(): void
    {
        $this->metricBuilder->addLog(new SqlLog('query'));
        $metric = $this->metricBuilder->build();

        static::assertInstanceOf(SqlMetric::class, $metric);
        static::assertEquals(1, $metric->count);
        static::assertEquals(
            ['query'],
            $metric->queries
        );
    }
}
