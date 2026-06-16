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
            new class($parameters) implements TestPreparedSubscriber {
                /**
                 * @var array<string, array<int, array{\ReflectionProperty, AutowireInterface}>>
                 */
                private array $propertyAttributes = [];

                /**
                 * @var array<string, array<string, mixed>>
                 */
                private array $propertyOptionsMap = [];

                public function __construct(
                    private ParameterCollection $parameters,
                ) {
                }

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

                    $propertyOptionsMap = $this->getPropertyOptionsMap($testCase, $test->methodName());

                    foreach ($this->getPropertyAttributes($testCase) as [$property, $autowire]) {
                        \assert($autowire instanceof AutowireInterface);

                        if ($autowire instanceof AutowireConfigurableInterface) {
                            $autowire->configure($this->parameters);
                        }

                        if ($autowire instanceof OptionAwareAutowireInterface) {
                            $autowire->setOptions($propertyOptionsMap[$property->getName()] ?? []);
                        }

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

                        foreach (new \ReflectionObject($testCase)->getProperties() as $property) {
                            foreach ($property->getAttributes() as $attribute) {
                                $reflectionClass = new \ReflectionClass($attribute->getName());

                                if (!$reflectionClass->implementsInterface(AutowireInterface::class)) {
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

                /**
                 * @return iterable<string, array<string, mixed>>
                 */
                private function getPropertyOptionsMap(AutowiredInterface $testCase, string $methodName): iterable
                {
                    $key = $testCase::class.'::'.$methodName;

                    if (!\array_key_exists($key, $this->propertyOptionsMap)) {
                        $reflectionClass = new \ReflectionClass($testCase);
                        $reflectionMethod = $reflectionClass->getMethod($methodName);

                        $optionsMap = [];

                        foreach ($reflectionMethod->getAttributes() as $attribute) {
                            $attributeInstance = $attribute->newInstance();

                            if (!$attributeInstance instanceof PropertyOptionAwareMethodAttributeInterface) {
                                continue;
                            }

                            foreach ($attributeInstance->getPropertyNames() as $propertyName) {
                                if (!\array_key_exists($propertyName, $optionsMap)) {
                                    $optionsMap[$propertyName] = [];
                                }

                                $optionsMap[$propertyName] = array_merge(
                                    $optionsMap[$propertyName],
                                    $attributeInstance->getOptions()
                                );
                            }
                        }

                        $this->propertyOptionsMap[$key] = $optionsMap;
                    }

                    return $this->propertyOptionsMap[$key];
                }
            },
        );
    }
}
