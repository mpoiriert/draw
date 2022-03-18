<?php

namespace Draw\Component\Core\Tests\Reflection;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use PHPUnit\Framework\TestCase;

class ReflectionAccessorTest extends TestCase
{
    private static $privateStaticProperty;

    private $privateProperty;

    private static function privateStaticFunction(string $argument = null): string
    {
        return $argument ?: 'private-static-function-value';
    }

    private function privateFunction(string $argument = null): string
    {
        return $argument ?: 'private-function-value';
    }

    public function testSetPropertyValue(): void
    {
        ReflectionAccessor::setPropertyValue($this, 'privateProperty', $value = 'private-property-value');

        $this->assertSame(
            $value,
            $this->privateProperty
        );
    }

    public function testGetPropertyValue(): void
    {
        $this->privateProperty = uniqid();
        $this->assertSame(
            $this->privateProperty,
            ReflectionAccessor::getPropertyValue($this, 'privateProperty')
        );
    }

    public function testSetPropertyValueStatic(): void
    {
        ReflectionAccessor::setPropertyValue($this, 'privateStaticProperty', $value = 'private-static-property-value');

        $this->assertSame(
            $value,
            $this::$privateStaticProperty
        );
    }

    public function testGetPropertyValueStatic(): void
    {
        $this::$privateStaticProperty = uniqid();
        $this->assertSame(
            $this::$privateStaticProperty,
            ReflectionAccessor::getPropertyValue($this, 'privateStaticProperty')
        );
    }

    public function testCallMethodStaticFunctionNoArgument(): void
    {
        $this->assertSame(
            static::privateStaticFunction(),
            ReflectionAccessor::callMethod($this, 'privateStaticFunction')
        );
    }

    public function testCallMethodStaticFunctionWithArgument(): void
    {
        $value = uniqid();
        $this->assertSame(
            static::privateStaticFunction($value),
            ReflectionAccessor::callMethod($this, 'privateStaticFunction', $value)
        );
    }

    public function testCallMethodFunctionNoArgument(): void
    {
        $this->assertSame(
            $this->privateFunction(),
            ReflectionAccessor::callMethod($this, 'privateFunction')
        );
    }

    public function testCallMethodFunctionWithArgument(): void
    {
        $value = uniqid();
        $this->assertSame(
            $this->privateFunction($value),
            ReflectionAccessor::callMethod($this, 'privateFunction', $value)
        );
    }
}
