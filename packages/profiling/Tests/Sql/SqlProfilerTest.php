<?php

namespace Draw\Component\Profiling\Tests\Sql;

use Draw\Component\Profiling\Sql\SqlProfiler;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class SqlProfilerTest extends TestCase
{
    private SqlProfiler&Stub $profiler;

    protected function setUp(): void
    {
        $this->profiler = static::createStub(SqlProfiler::class);
        $this->profiler
            ->method('getType')
            ->willReturn(SqlProfiler::PROFILER_TYPE)
        ;
    }

    public function testGetType(): void
    {
        static::assertSame(SqlProfiler::PROFILER_TYPE, $this->profiler->getType());
    }

    public function testStop(): void
    {
        $metric = $this->profiler->stop();

        static::assertSame(0, $metric->count);
    }
}
