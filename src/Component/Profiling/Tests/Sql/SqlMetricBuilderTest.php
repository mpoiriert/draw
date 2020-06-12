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

    public function setUp(): void
    {
        $this->metricBuilder = new SqlMetricBuilder();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(MetricBuilderInterface::class, $this->metricBuilder);
    }

    public function testBuild(): void
    {
        $this->metricBuilder->addLog(new SqlLog('query'));
        $metric = $this->metricBuilder->build();

        $this->assertInstanceOf(SqlMetric::class, $metric);
        $this->assertEquals(1, $metric->count);
        $this->assertEquals(
            ['query'],
            $metric->queries
        );
    }
}
