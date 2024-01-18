<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Finished as TestFinished;
use PHPUnit\Event\Test\FinishedSubscriber as TestFinishedSubscriber;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\FinishedSubscriber as TestSuiteFinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

class KernelShutdownExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscribers(
            new class() implements TestFinishedSubscriber {
                public function notify(TestFinished $event): void
                {
                    $test = $event->test();

                    \assert($test instanceof TestMethod);

                    KernelShutdownExtension::ensureKernelShutdown($test->className());
                }
            },
            new class() implements TestSuiteFinishedSubscriber {
                public function notify(TestSuiteFinished $event): void
                {
                    $class = $event->testSuite()->name();

                    KernelShutdownExtension::ensureKernelShutdown($class);
                }
            }
        );
    }

    public static function ensureKernelShutdown(string $class): void
    {
        if (!class_exists($class)) {
            return;
        }

        $reflection = new \ReflectionClass($class);

        if ($reflection->hasMethod('ensureKernelShutdown')) {
            $method = $reflection->getMethod('ensureKernelShutdown');
            $method->invoke(null);

            $reflection->getProperty('kernel')->setValue(null);
        }
    }
}
