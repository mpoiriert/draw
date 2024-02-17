<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Core\Reflection\ReflectionExtractor;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Prepared as TestPrepared;
use PHPUnit\Event\Test\PreparedSubscriber as TestPreparedSubscriber;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SetUpAutowireExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscribers(
            new class() implements TestPreparedSubscriber {
                /**
                 * @var array<string, array<int, array{\ReflectionProperty, string}>>
                 */
                private array $propertyAttributes = [];

                public function notify(TestPrepared $event): void
                {
                    $test = $event->test();

                    \assert($test instanceof TestMethod);

                    if (!is_a($test->className(), AutowireInterface::class, true)) {
                        return;
                    }

                    $testCase = null;

                    foreach (debug_backtrace() as $frame) {
                        if (isset($frame['object']) && $frame['object'] instanceof TestCase) {
                            $testCase = $frame['object'];
                            break;
                        }
                    }

                    if (!$testCase instanceof AutowireInterface) {
                        return;
                    }

                    if (!$testCase instanceof KernelTestCase) {
                        return;
                    }

                    $container = null;

                    foreach ($this->getPropertyAttributes($testCase) as [$property, $serviceId]) {
                        $container ??= ReflectionAccessor::callMethod($testCase, 'getContainer');

                        $property->setValue(
                            $testCase,
                            $container->get($serviceId)
                        );
                    }
                }

                /**
                 * @return iterable<array{0:\ReflectionProperty, 1: string}>
                 */
                private function getPropertyAttributes(TestCase $testCase): iterable
                {
                    $className = $testCase::class;

                    if (!\array_key_exists($className, $this->propertyAttributes)) {
                        $this->propertyAttributes[$className] = [];

                        foreach ((new \ReflectionObject($testCase))->getProperties() as $property) {
                            $attribute = $property->getAttributes(AutowireService::class, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

                            if (!$attribute) {
                                continue;
                            }

                            $autoWireService = $attribute->newInstance();

                            $serviceId = $autoWireService->getServiceId();

                            if (!$serviceId) {
                                $classes = ReflectionExtractor::getClasses($property->getType());
                                if (1 !== \count($classes)) {
                                    throw new \RuntimeException('Property '.$property->getName().' of class '.$testCase::class.' must have a type hint.');
                                }

                                $serviceId = $classes[0];
                            }

                            $this->propertyAttributes[$className][] = [$property, $serviceId];
                        }
                    }

                    foreach ($this->propertyAttributes[$className] as $property) {
                        yield $property;
                    }
                }
            },
        );
    }
}
