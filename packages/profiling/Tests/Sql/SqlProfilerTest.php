<?php

namespace Draw\Component\Profiling\Tests\Sql;

use Draw\Component\Profiling\Sql\SqlMetric;
use Draw\Component\Profiling\Sql\SqlMetricBuilder;
use Draw\Component\Profiling\Sql\SqlProfiler;
use PHPUnit\Framework\TestCase;

class SqlProfilerTest extends TestCase
{
    /**
     * @var SqlProfiler
     */
    private $profiler;

    public function setUp(): void
    {
        $mock = $this->getMockForAbstractClass(SqlProfiler::class);
        $this->profiler = $mock;
    }

    public function testGetType(): void
    {
        $this->assertSame(SqlProfiler::PROFILER_TYPE, $this->profiler->getType());
    }

    public function testGetMetricBuilder(): void
    {
        $metricBuilder = $this->profiler->getMetricBuilder();

        $this->assertInstanceOf(SqlMetricBuilder::class, $metricBuilder);
        $this->assertSame($metricBuilder, $this->profiler->getMetricBuilder());
    }

    public function testStop(): void
    {
        $metric = $this->profiler->stop();

        $this->assertInstanceOf(SqlMetric::class, $metric);
        $this->assertSame(0, $metric->count);
    }
}
