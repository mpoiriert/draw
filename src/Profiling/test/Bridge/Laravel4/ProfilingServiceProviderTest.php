<?php

namespace Draw\Profiling\Bridge\Laravel4;

use Draw\DataTester\Tester;
use Draw\Profiling\MetricAggregatorProfiler;
use Draw\Profiling\Sql\SqlAssertionBuilder;
use Illuminate\Events\Dispatcher;
use Orchestra\Testbench\TestCaseInterface;
use Orchestra\Testbench\Traits\ApplicationClientTrait;
use Orchestra\Testbench\Traits\ApplicationTrait;
use Orchestra\Testbench\Traits\PHPUnitAssertionsTrait;
use PHPUnit\Framework\TestCase;

class ProfilingServiceProviderTest extends TestCase implements TestCaseInterface
{
    use ApplicationClientTrait, ApplicationTrait, PHPUnitAssertionsTrait;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        if (!$this->app) {
            $this->refreshApplication();
        }
    }

    public function getEnvironmentSetUp($app)
    {
    }

    /**
     * @return MetricAggregatorProfiler
     */
    public function getService()
    {
        return $this->app['draw.profiler'];
    }

    protected function getPackageProviders()
    {
        return [ProfilingServiceProvider::class];
    }

    public function testGetService()
    {
        $this->assertInstanceOf(MetricAggregatorProfiler::class, $this->getService());
    }

    public function testServiceIsStarted()
    {
        $this->assertTrue($this->app['draw.profiler']->isStarted());
    }

    public function testStop()
    {
        $this->getService()->stop();
        $this->assertFalse($this->getService()->isStarted());
    }

    public function testSqlProfiler()
    {
        $this->app[Dispatcher::class]->fire(
            'illuminate.query',
            [
                'select * from test ?',
                ['value' => 'value'],
                1.2,
                'mysql'
            ]
        );

        $metric = $this->getService()->stop();

        $this->assertFalse($this->getService()->isStarted());

        (new Tester($metric))->test(SqlAssertionBuilder::create(1));
    }
}