<?php

namespace Draw\Component\Core\Reflection;

use ReflectionMethod;
use ReflectionProperty;

class ReflectionAccessor
{
    public static function callMethod($objectOrClass, string $methodName, ...$arguments)
    {
        $methodReflection = static::createAccessibleMethodReflection($objectOrClass, $methodName);

        $object = $methodReflection->isStatic() ? null : $objectOrClass;

        return $methodReflection->invoke($object, ...$arguments);
    }

    public static function getPropertyValue($objectOrClass, string $propertyName)
    {
        $property = static::createAccessiblePropertyReflection($objectOrClass, $propertyName);

        return $property->isStatic()
            ? $property->getValue()
            : $property->getValue($objectOrClass);
    }

    public static function setPropertyValue($objectOrProperty, string $propertyName, $value): void
    {
        $property = static::createAccessiblePropertyReflection($objectOrProperty, $propertyName);

        $property->isStatic()
            ? $property->setValue($value)
            : $property->setValue($objectOrProperty, $value);
    }

    private static function createAccessibleMethodReflection($objectOrClass, string $methodName): ReflectionMethod
    {
        $class = is_object($objectOrClass) ? get_class($objectOrClass) : $objectOrClass;

        $reflectionMethod = new ReflectionMethod($class, $methodName);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod;
    }

    private static function createAccessiblePropertyReflection($objectOrClass, string $propertyName): ReflectionProperty
    {
        $class = is_object($objectOrClass) ? get_class($objectOrClass) : $objectOrClass;

        $reflectionProperty = new ReflectionProperty($class, $propertyName);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty;
    }
}
