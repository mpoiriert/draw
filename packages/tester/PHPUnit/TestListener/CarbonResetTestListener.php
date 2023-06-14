<?php

/*
 * (c)Copyright UGroupMedia Inc. <dev@ugroupmedia.com>
 * This source file is part of PNP Project and is subject to
 * copyright. It can not be copied and/or distributed without
 * the express permission of UGroupMedia Inc.
 * If you get a copy of this file without explicit authorization,
 * please contact us to the email above.
 */

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
