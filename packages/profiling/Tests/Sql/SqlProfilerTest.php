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
        static::assertSame(SqlProfiler::PROFILER_TYPE, $this->profiler->getType());
    }

    public function testGetMetricBuilder(): void
    {
        $metricBuilder = $this->profiler->getMetricBuilder();

        static::assertInstanceOf(SqlMetricBuilder::class, $metricBuilder);
        static::assertSame($metricBuilder, $this->profiler->getMetricBuilder());
    }

    public function testStop(): void
    {
        $metric = $this->profiler->stop();

        static::assertInstanceOf(SqlMetric::class, $metric);
        static::assertSame(0, $metric->count);
    }
}
