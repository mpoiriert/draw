<?php

namespace Draw\Component\Profiling\Tests;

use Draw\Component\Profiling\ProfilerCoordinator;
use Draw\Component\Profiling\ProfilerInterface;
use PHPUnit\Framework\TestCase;

class ProfilerCoordinatorTest extends TestCase
{
    private const PROFILER_TYPE = 'test';

    /**
     * @var ProfilerCoordinator
     */
    private $profilerCoordinator;

    /**
     * @var ProfilerInterface
     */
    private $profiler;

    public function setUp(): void
    {
        $this->profilerCoordinator = new ProfilerCoordinator();
        $this->profiler = $this->createMock(ProfilerInterface::class);
    }

    public function testIsStartedDefault(): void
    {
        $this->assertFalse($this->profilerCoordinator->isStarted());
    }

    public function testIsStartedAfterStart(): void
    {
        $this->profilerCoordinator->startAll();
        $this->assertTrue($this->profilerCoordinator->isStarted());
    }

    public function testIsStartedAfterStop(): void
    {
        $this->profilerCoordinator->startAll();
        $this->profilerCoordinator->stopAll();
        $this->assertFalse($this->profilerCoordinator->isStarted());
    }

    public function testRegisterProfile()
    {
        $this->profiler->expects($this->once())->method('getType')->willReturn(self::PROFILER_TYPE);
        $this->profilerCoordinator->registerProfiler($this->profiler);
    }

    public function testStarAll()
    {
        $this->testRegisterProfile();
        $this->profiler->expects($this->once())->method('start');
        $this->profilerCoordinator->startAll();
    }

    public function testStopAll()
    {
        $this->testStarAll();
        $this->profiler->expects($this->once())->method('stop')->willReturn($result = 'result');
        $metrics = $this->profilerCoordinator->stopAll();

        $this->assertObjectHasAttribute(self::PROFILER_TYPE, $metrics);
        $this->assertSame($result, $metrics->{self::PROFILER_TYPE});
    }
}
