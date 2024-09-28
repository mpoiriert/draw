<?php

namespace Draw\Bundle\TesterBundle\Profiling;

use Draw\Component\Profiling\ProfilerCoordinator;
use Draw\Component\Tester\DataTester;

trait MetricTesterTrait
{
    public function assertMetrics($metricAssertions): void
    {
        if (!\is_array($metricAssertions)) {
            $metricAssertions = [$metricAssertions];
        }

        $metrics = static::getContainer()->get(ProfilerCoordinator::class)->stopAll();
        $tester = new DataTester($metrics);

        foreach ($metricAssertions as $metricAssertion) {
            $tester->test($metricAssertion);
        }
    }
}
