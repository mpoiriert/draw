<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

use Draw\Bundle\TesterBundle\WebTestCase as DrawWebTestCase;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Core\Reflection\ReflectionExtractor;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Prepared as TestPrepared;
use PHPUnit\Event\Test\PreparedSubscriber as TestPreparedSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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

                /**
                 * @var array<string, array<int, array{\ReflectionProperty, AutowireMockProperty}>>
                 */
                private array $propertyMocks = [];

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

                    if (!$testCase instanceof KernelTestCase) {
                        return;
                    }

                    $this->initializeClients($testCase);

                    $container = null;

                    foreach ($this->getPropertyAttributes($testCase) as [$property, $serviceId]) {
                        $container ??= ReflectionAccessor::callMethod($testCase, 'getContainer');

                        if ($serviceId instanceof AutowireMock) {
                            $property->setValue(
                                $testCase,
                                $this->getMockFor($testCase, $property->getName())
                            );

                            continue;
                        }

                        $property->setValue(
                            $testCase,
                            $container->get($serviceId)
                        );
                    }

                    foreach ($this->getPropertyMockAttributes($testCase) as [$property, $autoWireMockProperty]) {
                        $service = $property->getValue($testCase);

                        ReflectionAccessor::setPropertyValue(
                            $service,
                            $autoWireMockProperty->getProperty(),
                            ReflectionAccessor::getPropertyValue(
                                $testCase,
                                $autoWireMockProperty->getFromProperty()
                            )
                        );
                    }

                    if ($testCase instanceof AutowiredCompletionAwareInterface) {
                        $testCase->postAutowire();
                    }
                }

                private function initializeClients(TestCase $testCase): void
                {
                    if (!$testCase instanceof WebTestCase && !$testCase instanceof DrawWebTestCase) {
                        if (!empty(iterator_to_array($this->getClientAttributes($testCase), false))) {
                            throw new \RuntimeException(
                                sprintf(
                                    'AutowireClient attribute can only be used in %s or %s.',
                                    WebTestCase::class,
                                    DrawWebTestCase::class
                                )
                            );
                        }

                        return;
                    }

                    foreach ($this->getClientAttributes($testCase) as [$property, $attribute]) {
                        \assert($property instanceof \ReflectionProperty);
                        \assert($attribute instanceof \ReflectionAttribute);

                        $autoWireClient = $attribute->newInstance();
                        \assert($autoWireClient instanceof AutowireClient);

                        $property->setValue(
                            $testCase,
                            ReflectionAccessor::callMethod(
                                $testCase,
                                'createClient',
                                $autoWireClient->getOptions(),
                                $autoWireClient->getServer()
                            )
                        );
                    }
                }

                private function getClientAttributes(TestCase $testCase): \Generator
                {
                    foreach ((new \ReflectionObject($testCase))->getProperties() as $property) {
                        $attribute = $property->getAttributes(
                            AutowireClient::class,
                            \ReflectionAttribute::IS_INSTANCEOF
                        )[0] ?? null;

                        if (null !== $attribute) {
                            yield [$property, $attribute];
                        }
                    }
                }

                /**
                 * @return iterable<array{\ReflectionProperty, string|AutowireMock}>
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

                            if ($autoWireService instanceof AutowireMock) {
                                $this->propertyAttributes[$className][] = [$property, $autoWireService];

                                continue;
                            }

                            $serviceId = $autoWireService->getServiceId();

                            if (!$serviceId) {
                                $classes = ReflectionExtractor::getClasses($property->getType());
                                if (1 !== \count($classes)) {
                                    throw new \RuntimeException('Property '.$property->getName().' of class '.$testCase::class.' must have a type hint.');
                                }

                                $serviceId = $classes[0];
                            }

                            $this->propertyAttributes[$className][] = [$property, $serviceId];

                            foreach ($property->getAttributes(AutowireMockProperty::class) as $attribute) {
                                $autoWireMockProperty = $attribute->newInstance();
                                $this->propertyMocks[$className][] = [$property, $autoWireMockProperty];
                            }
                        }
                    }

                    foreach ($this->propertyAttributes[$className] as $property) {
                        yield $property;
                    }
                }

                /**
                 * @return iterable<array{\ReflectionProperty, AutowireMockProperty}>
                 */
                private function getPropertyMockAttributes(TestCase $testCase): iterable
                {
                    yield from $this->propertyMocks[$testCase::class] ?? [];
                }

                private function getMockFor(TestCase $testCase, string $property)
                {
                    $reflectionProperty = new \ReflectionProperty($testCase, $property);

                    $type = $reflectionProperty->getType();

                    if (!$type instanceof \ReflectionIntersectionType) {
                        throw new \RuntimeException('Property '.$property.' of class '.$testCase::class.' must have a type hint intersection with Mock.');
                    }

                    $types = $type->getTypes();

                    if (2 !== \count($types)) {
                        throw new \RuntimeException('Property '.$property.' of class '.$testCase::class.' can only have 2 intersection types.');
                    }

                    foreach ($types as $type) {
                        if (!$type instanceof \ReflectionNamedType) {
                            throw new \RuntimeException('Property '.$property.' of class '.$testCase::class.' intersction must be of named type.');
                        }

                        if (MockObject::class === $type->getName()) {
                            continue;
                        }

                        $reflectionProperty->setValue(
                            $testCase,
                            $mock = ReflectionAccessor::callMethod($testCase, 'createMock', $type->getName())
                        );

                        return $mock;
                    }
                }
            },
        );
    }
}
