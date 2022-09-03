<?php

namespace Draw\Component\Tester;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;

trait MockTrait
{
    abstract public function getMockBuilder(string $className): MockBuilder;

    abstract protected function createMock(string $originalClassName): MockObject;

    /**
     * @template T of object
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

    public function mockProperty(object $object, string $property, string $originalClassName): MockObject
    {
        ReflectionAccessor::setPropertyValue(
            $object,
            $property,
            $mock = $this->createMock($originalClassName)
        );

        return $mock;
    }
}
