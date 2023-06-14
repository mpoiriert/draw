<?php

namespace Draw\Component\Tester\PHPUnit\TestListener;

use Carbon\Carbon;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestSuite;

/**
 * Reset carbon on test and test suite end.
 */
class CarbonResetTestListener implements TestListener
{
    use TestListenerDefaultImplementation;

    public function endTest(Test $test, float $time): void
    {
        Carbon::setTestNow(null);
    }

    public function endTestSuite(TestSuite $suite): void
    {
        Carbon::setTestNow(null);
    }
}
