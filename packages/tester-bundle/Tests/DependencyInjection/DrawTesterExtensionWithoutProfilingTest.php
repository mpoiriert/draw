<?php

namespace Draw\Bundle\TesterBundle\Tests\DependencyInjection;

use Draw\Bundle\TesterBundle\Profiling\SqlProfiler;
use Draw\Component\Profiling\ProfilerCoordinator;

class DrawTesterExtensionWithoutProfilingTest extends DrawTesterExtensionTest
{
    public function getConfiguration(): array
    {
        return ['profiling' => ['enabled' => false]];
    }

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield from static::removeProvidedService(
            [
                SqlProfiler::class,
                \Draw\Component\Profiling\Sql\SqlProfiler::class,
                ProfilerCoordinator::class,
            ],
            parent::provideTestHasServiceDefinition()
        );
    }
}
