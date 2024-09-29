<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\DoctrineTransaction;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\FinishedSubscriber as TestSuiteFinishedSubscriber;
use PHPUnit\Event\TestSuite\Started as TestSuiteStarted;
use PHPUnit\Event\TestSuite\StartedSubscriber as TestSuiteStartedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class DoctrineTransactionExtension implements Extension
{
    public static bool $transactionStarted = false;

    public static function rollBack(): void
    {
        if (!self::$transactionStarted) {
            return;
        }

        StaticDriver::rollBack();
        self::$transactionStarted = false;
    }

    public static function begin(): void
    {
        if (self::$transactionStarted) {
            return;
        }

        StaticDriver::beginTransaction();
        self::$transactionStarted = true;
    }

    #[\Override]
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscribers(
            new class implements TestSuiteStartedSubscriber {
                public function notify(TestSuiteStarted $event): void
                {
                    $class = $event->testSuite()->name();

                    DoctrineTransactionExtension::startTransactionIfNeeded($class);
                }
            },
            new class implements TestSuiteFinishedSubscriber {
                public function notify(TestSuiteFinished $event): void
                {
                    DoctrineTransactionExtension::rollBack();
                    StaticDriver::setKeepStaticConnections(false);
                }
            },
        );
    }

    private static function asNoTransaction(string $class): bool
    {
        if (!class_exists($class)) {
            return false;
        }

        return (bool) \count((new \ReflectionClass($class))->getAttributes(NoTransaction::class));
    }

    public static function startTransactionIfNeeded(string $class): void
    {
        if (self::asNoTransaction($class)) {
            return;
        }

        StaticDriver::setKeepStaticConnections(true);

        static::begin();
    }
}
