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
    private const PROFILER_TYPE = 'test';

    private ProfilerCoordinator $object;

    /**
     * @var ProfilerInterface&MockObject
     */
    private ProfilerInterface $profiler;

    protected function setUp(): void
    {
        $this->object = new ProfilerCoordinator();
        $this->profiler = $this->createMock(ProfilerInterface::class);
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
        $this->profiler->expects($this->once())->method('getType')->willReturn(self::PROFILER_TYPE);
        $this->object->registerProfiler($this->profiler);
    }

    public function testStarAll(): void
    {
        $this->testRegisterProfile();
        $this->profiler->expects($this->once())->method('start');
        $this->object->startAll();
    }

    public function testStopAll(): void
    {
        $this->testStarAll();
        $this->profiler->expects($this->once())->method('stop')->willReturn($result = 'result');
        $metrics = $this->object->stopAll();

        $this->assertTrue(isset($metrics->{self::PROFILER_TYPE}));
        $this->assertSame($result, $metrics->{self::PROFILER_TYPE});
    }
}
