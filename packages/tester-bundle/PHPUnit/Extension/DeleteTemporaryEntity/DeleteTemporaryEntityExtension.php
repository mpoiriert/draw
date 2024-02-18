<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\DeleteTemporaryEntity;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\FinishedSubscriber as TestSuiteFinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DeleteTemporaryEntityExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscribers(
            new class() implements TestSuiteFinishedSubscriber {
                public function notify(TestSuiteFinished $event): void
                {
                    $class = $event->testSuite()->name();

                    if (!class_exists($class)) {
                        return;
                    }

                    if (!is_a($class, KernelTestCase::class, true)) {
                        return;
                    }

                    $container = ReflectionAccessor::callMethod(
                        $class,
                        'getContainer',
                    );

                    \assert($container instanceof ContainerInterface);

                    try {
                        $temporaryEntityFactory = $container->get(TemporaryEntityCleanerInterface::class);

                        \assert($temporaryEntityFactory instanceof TemporaryEntityCleanerInterface);

                        $temporaryEntityFactory->deleteTemporaryEntities();

                    } catch (\Throwable $error) {
                        throw new \RuntimeException(
                            'Failed to delete temporary entities in '.$class.'. '.$error->getMessage(),
                            previous: $error
                        );
                    }
                }
            }
        );
    }
}
