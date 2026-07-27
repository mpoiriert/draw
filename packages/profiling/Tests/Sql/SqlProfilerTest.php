<?php

namespace Draw\Component\Profiling\Tests\Sql;

use Draw\Component\Profiling\Sql\SqlMetric;
use Draw\Component\Profiling\Sql\SqlMetricBuilder;
use Draw\Component\Profiling\Sql\SqlProfiler;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class SqlProfilerTest extends TestCase
{
    use MockTrait;

    private SqlProfiler&MockObject $profiler;

    protected function setUp(): void
    {
        $this->profiler = $this->createMock(SqlProfiler::class);
        $this->profiler
            ->method('getType')
            ->willReturn(SqlProfiler::PROFILER_TYPE)
        ;
    }

    public function testGetType(): void
    {
        $this->assertSame(SqlProfiler::PROFILER_TYPE, $this->profiler->getType());
    }

    public function testGetMetricBuilder(): void
    {
        $this->assertInstanceOf(SqlMetricBuilder::class, $this->profiler->getMetricBuilder());
    }

    public function testStop(): void
    {
        $metric = $this->profiler->stop();

        $this->assertInstanceOf(SqlMetric::class, $metric);
        $this->assertSame(0, $metric->count);
    }
}
