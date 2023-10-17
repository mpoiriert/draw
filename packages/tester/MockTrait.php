<?php

namespace Draw\Component\Tester;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;

trait MockTrait
{
    abstract public function getMockBuilder(string $className): MockBuilder;

    abstract protected function createMock(string $originalClassName): MockObject;

    /**
     * @template T of object
     *
     * @phpstan-param class-string<T> $originalClassName
     *
     * @return MockObject&T
     */
    protected function createMockWithExtraMethods(string $originalClassName, array $methods): MockObject
    {
        return $this->getMockBuilder($originalClassName)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->onlyMethods(get_class_methods($originalClassName))
            ->addMethods($methods)
            ->getMock();
    }

    /**
     * @template T of object
     *
     * @phpstan-param class-string<T> $originalClassName
     *
     * @return MockObject&T
     */
    public function mockProperty(object $object, string $property, string $originalClassName): MockObject
    {
        ReflectionAccessor::setPropertyValue(
            $object,
            $property,
            $mock = $this->createMock($originalClassName)
        );

        return $mock;
    }

    public static function withConsecutive(array $firstCallArguments, array ...$consecutiveCallsArguments): iterable
    {
        foreach ($consecutiveCallsArguments as $consecutiveCallArguments) {
            self::assertGreaterThanOrEqual(
                \count($firstCallArguments),
                \count($consecutiveCallArguments),
                'Arguments for consecutive calls must be greater or equal to the first call arguments.'
            );
        }

        $allConsecutiveCallsArguments = [$firstCallArguments, ...$consecutiveCallsArguments];

        $numberOfArguments = \count($firstCallArguments);
        $mockedMethodCall = 0;
        $callbackCall = 0;
        foreach (array_keys($firstCallArguments) as $index) {
            yield new Callback(
                static function (mixed $actualArgument) use ($allConsecutiveCallsArguments, &$mockedMethodCall, &$callbackCall, $index, $numberOfArguments): bool {
                    $previousMockedMethodCall = $mockedMethodCall;
                    ++$callbackCall;
                    $mockedMethodCall = (int) ($callbackCall / $numberOfArguments);

                    if (!\array_key_exists($previousMockedMethodCall, $allConsecutiveCallsArguments)) {
                        return true;
                    }

                    if (!\array_key_exists($index, $allConsecutiveCallsArguments[$previousMockedMethodCall])) {
                        return true;
                    }

                    $expected = $allConsecutiveCallsArguments[$previousMockedMethodCall][$index];

                    if ($expected instanceof Constraint) {
                        self::assertThat($actualArgument, $expected);
                    } else {
                        self::assertEquals($expected, $actualArgument);
                    }

                    return true;
                },
            );
        }
    }
}
