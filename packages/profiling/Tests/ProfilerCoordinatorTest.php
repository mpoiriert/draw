<?php

namespace Draw\Component\Profiling\Tests;

use Draw\Component\Profiling\ProfilerCoordinator;
use Draw\Component\Profiling\ProfilerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ProfilerCoordinatorTest extends TestCase
{
    private const string PROFILER_TYPE = 'test';

    private ProfilerCoordinator $object;

    protected function setUp(): void
    {
        $this->object = new ProfilerCoordinator();
    }

    public function testIsStartedDefault(): void
    {
        $this->assertFalse($this->object->isStarted());
    }

    public function testIsStartedAfterStart(): void
    {
        $this->object->startAll();
        $this->assertTrue($this->object->isStarted());
    }

    public function testIsStartedAfterStop(): void
    {
        $this->object->startAll();
        $this->object->stopAll();
        $this->assertFalse($this->object->isStarted());
    }

    public function testRegisterProfile(): void
    {
        $this->doTestRegisterProfile();
    }

    public function testStarAll(): void
    {
        $this->doTestStartAll();
    }

    public function testStopAll(): void
    {
        $profiler = $this->doTestStartAll();
        $profiler
            ->expects($this->once())
            ->method('stop')
            ->willReturn($result = 'result')
        ;

        $metrics = $this->object->stopAll();

        $this->assertTrue(isset($metrics->{self::PROFILER_TYPE}));
        $this->assertSame($result, $metrics->{self::PROFILER_TYPE});
    }

    private function doTestRegisterProfile(): ProfilerInterface&MockObject
    {
        $profiler = $this->createMock(ProfilerInterface::class);
        $profiler
            ->expects($this->once())
            ->method('getType')
            ->willReturn(self::PROFILER_TYPE)
        ;

        $this->object->registerProfiler($profiler);

        return $profiler;
    }

    private function doTestStartAll(): ProfilerInterface&MockObject
    {
        $profiler = $this->doTestRegisterProfile();
        $profiler
            ->expects($this->once())
            ->method('start')
        ;

        $this->object->startAll();

        return $profiler;
    }
}
