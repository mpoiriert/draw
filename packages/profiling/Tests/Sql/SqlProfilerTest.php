<?php

namespace Draw\Component\Profiling\Tests\Sql;

use Draw\Component\Profiling\Sql\SqlMetricBuilder;
use Draw\Component\Profiling\Sql\SqlProfiler;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class SqlProfilerTest extends TestCase
{
    private SqlProfiler $profiler;

    protected function setUp(): void
    {
        $this->profiler = new class extends SqlProfiler {
            public function start(): void
            {
            }
        };
    }

    public function testGetMetricBuilder(): void
    {
        $this->assertInstanceOf(SqlMetricBuilder::class, $this->profiler->getMetricBuilder());
    }

    public function testGetType(): void
    {
        $this->assertSame(SqlProfiler::PROFILER_TYPE, $this->profiler->getType());
    }

    public function testStop(): void
    {
        $this->assertSame(
            0,
            $this->profiler->stop()->count
        );
    }
}
