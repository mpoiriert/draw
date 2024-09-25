<?php

namespace Draw\Component\Profiling\Tests\Sql;

use Draw\Component\Profiling\Sql\SqlMetric;
use Draw\Component\Profiling\Sql\SqlMetricBuilder;
use Draw\Component\Profiling\Sql\SqlProfiler;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SqlProfilerTest extends TestCase
{
    use MockTrait;

    private SqlProfiler&MockObject $profiler;

    protected function setUp(): void
    {
        $this->profiler = $this->createMock(SqlProfiler::class);
        $this->profiler
            ->method('getType')
            ->willReturn(SqlProfiler::PROFILER_TYPE);
    }

    public function testGetType(): void
    {
        static::assertSame(SqlProfiler::PROFILER_TYPE, $this->profiler->getType());
    }

    public function testGetMetricBuilder(): void
    {
        static::assertInstanceOf(SqlMetricBuilder::class, $this->profiler->getMetricBuilder());
    }

    public function testStop(): void
    {
        $metric = $this->profiler->stop();

        static::assertInstanceOf(SqlMetric::class, $metric);
        static::assertSame(0, $metric->count);
    }
}
