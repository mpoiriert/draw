<?php

namespace Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Prepared as TestPrepared;
use PHPUnit\Event\Test\PreparedSubscriber as TestPreparedSubscriber;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

class SetUpAutowireExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscribers(
            new class implements TestPreparedSubscriber {
                /**
                 * @var array<string, array<int, array{\ReflectionProperty, AutowireInterface}>>
                 */
                private array $propertyAttributes = [];

                public function notify(TestPrepared $event): void
                {
                    $test = $event->test();

                    \assert($test instanceof TestMethod);

                    if (!is_a($test->className(), AutowiredInterface::class, true)) {
                        return;
                    }

                    $testCase = null;

                    foreach (debug_backtrace() as $frame) {
                        if (isset($frame['object']) && $frame['object'] instanceof TestCase) {
                            $testCase = $frame['object'];
                            break;
                        }
                    }

                    if (!$testCase instanceof AutowiredInterface) {
                        return;
                    }

                    foreach ($this->getPropertyAttributes($testCase) as [$property, $autowire]) {
                        \assert($autowire instanceof AutowireInterface);

                        $autowire->autowire($testCase, $property);
                    }

                    if ($testCase instanceof AutowiredCompletionAwareInterface) {
                        $testCase->postAutowire();
                    }
                }

                /**
                 * @return iterable<array{\ReflectionProperty, AutowireInterface}>
                 */
                private function getPropertyAttributes(TestCase $testCase): iterable
                {
                    $className = $testCase::class;

                    if (!\array_key_exists($className, $this->propertyAttributes)) {
                        $autowireAttributes = [];

                        foreach ((new \ReflectionObject($testCase))->getProperties() as $property) {
                            foreach ($property->getAttributes() as $attribute) {
                                $attributeClass = $attribute->getName();

                                if (!(new \ReflectionClass($attributeClass))->implementsInterface(AutowireInterface::class)) {
                                    continue;
                                }

                                $autowireAttributes[] = [$property, $attribute->newInstance()];
                            }
                        }

                        usort($autowireAttributes, static fn ($a, $b) => $a[1]::getPriority() <=> $b[1]::getPriority());

                        // We reverse because priority 1 comes before priority 0
                        $this->propertyAttributes[$className] = array_reverse($autowireAttributes);
                    }

                    foreach ($this->propertyAttributes[$className] as $property) {
                        yield $property;
                    }
                }
            },
        );
    }
}
