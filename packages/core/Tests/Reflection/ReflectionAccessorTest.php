<?php

namespace Draw\Component\Core\Tests\Reflection;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * @covers \Draw\Component\Core\Reflection\ReflectionAccessor
 */
class ReflectionAccessorTest extends TestCase
{
    private static ?string $privateStaticProperty = null;

    private ?string $privateProperty = null;

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
        ReflectionAccessor::setPropertyValue($this, 'privateProperty', $value = uniqid());

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
        ReflectionAccessor::setPropertyValue($this, 'privateStaticProperty', $value = uniqid());

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

    public function testSetPropertiesValue(): void
    {
        ReflectionAccessor::setPropertiesValue(
            $this,
            [
                'privateProperty' => $instance = uniqid(),
                'privateStaticProperty' => $static = uniqid(),
            ]
        );

        $this->assertSame(
            $instance,
            $this->privateProperty
        );

        $this->assertSame(
            $static,
            static::$privateStaticProperty
        );
    }

    public function testPropertyValueException(): void
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('Property Draw\Component\Core\Tests\Reflection\ReflectionAccessorTest::$invalidProperty does not exist');
        ReflectionAccessor::getPropertyValue($this, 'invalidProperty');
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
        $this->assertSame(
            static::privateStaticFunction($value = uniqid()),
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
        $this->assertSame(
            $this->privateFunction($value = uniqid()),
            ReflectionAccessor::callMethod($this, 'privateFunction', $value)
        );
    }

    public function testCallMethodException(): void
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('Method Draw\Component\Core\Tests\Reflection\ReflectionAccessorTest::invalidMethod() does not exist');
        ReflectionAccessor::callMethod($this, 'invalidMethod');
    }
}
