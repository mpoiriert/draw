<?php

namespace Draw\Component\Profiling\Tests;

use Draw\Component\Profiling\ProfilerCoordinator;
use Draw\Component\Profiling\ProfilerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class ProfilerCoordinatorTest extends TestCase
{
    private const PROFILER_TYPE = 'test';

    /**
     * @var ProfilerCoordinator
     */
    private $profilerCoordinator;

    /**
     * @var ObjectProphecy
     */
    private $profilerProphecy;

    /**
     * @var ProfilerInterface
     */
    private $profiler;

    public function setUp(): void
    {
        $this->profilerCoordinator = new ProfilerCoordinator();
        $this->profilerProphecy = $this->prophesize(ProfilerInterface::class);
        $this->profiler = $this->profilerProphecy->reveal();
    }

    public function testIsStarted_default(): void
    {
        $this->assertFalse($this->profilerCoordinator->isStarted());
    }

    public function testIsStarted_afterStart(): void
    {
        $this->profilerCoordinator->startAll();
        $this->assertTrue($this->profilerCoordinator->isStarted());
    }

    public function testIsStarted_afterStop(): void
    {
        $this->profilerCoordinator->startAll();
        $this->profilerCoordinator->stopAll();
        $this->assertFalse($this->profilerCoordinator->isStarted());
    }

    public function testRegisterProfile()
    {
        $this->profilerProphecy->__call('getType', [])->shouldBeCalled()->willReturn(self::PROFILER_TYPE);
        $this->profilerCoordinator->registerProfiler($this->profiler);
    }

    public function testStarAll()
    {
        $this->testRegisterProfile();
        $this->profilerProphecy->__call('start', [])->shouldBeCalled();
        $this->profilerCoordinator->startAll();
    }

    public function testStopAll()
    {
        $this->testStarAll();
        $this->profilerProphecy->__call('stop', [])->shouldBeCalledOnce()->willReturn($result = 'result');
        $metrics = $this->profilerCoordinator->stopAll();

        $this->assertObjectHasAttribute(self::PROFILER_TYPE, $metrics);
        $this->assertSame($result, $metrics->{self::PROFILER_TYPE});
    }
}
