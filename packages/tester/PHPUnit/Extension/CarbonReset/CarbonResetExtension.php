<?php

namespace Draw\Component\Tester\PHPUnit\Extension\CarbonReset;

use Carbon\Carbon;
use PHPUnit\Event\Test\Finished as TestFinished;
use PHPUnit\Event\Test\FinishedSubscriber as TestFinishedSubscriber;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\FinishedSubscriber as TestSuiteFinishedSubscriber;
use PHPUnit\Runner;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

class CarbonResetExtension implements Runner\Extension\Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscribers(
            new class() implements TestFinishedSubscriber {
                public function notify(TestFinished $event): void
                {
                    Carbon::setTestNow(null);
                }
            },
            new class() implements TestSuiteFinishedSubscriber {
                public function notify(TestSuiteFinished $event): void
                {
                    Carbon::setTestNow(null);
                }
            }
        );
    }
}
