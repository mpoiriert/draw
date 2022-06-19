<?php

namespace Draw\Component\Core\Reflection;

final class ReflectionAccessor
{
    public static function callMethod($objectOrClass, string $methodName, ...$arguments)
    {
        $methodReflection = self::createAccessibleMethodReflection($objectOrClass, $methodName);

        $object = $methodReflection->isStatic() ? null : $objectOrClass;

        return $methodReflection->invoke($object, ...$arguments);
    }

    public static function getPropertyValue($objectOrClass, string $propertyName)
    {
        $property = self::createAccessiblePropertyReflection($objectOrClass, $propertyName);

        return $property->isStatic()
            ? $property->getValue()
            : $property->getValue($objectOrClass);
    }

    public static function setPropertyValue($objectOrClass, string $propertyName, $value): void
    {
        $property = self::createAccessiblePropertyReflection($objectOrClass, $propertyName);

        $property->isStatic()
            ? $property->setValue($value)
            : $property->setValue($objectOrClass, $value);
    }

    public static function setPropertiesValue($objectOrClass, array $map): void
    {
        foreach ($map as $property => $value) {
            self::setPropertyValue($objectOrClass, $property, $value);
        }
    }

    private static function createAccessibleMethodReflection($objectOrClass, string $methodName): \ReflectionMethod
    {
        $class = \is_object($objectOrClass) ? \get_class($objectOrClass) : $objectOrClass;

        $reflectionClass = new \ReflectionClass($class);

        while (true) {
            if ($reflectionClass->hasMethod($methodName)) {
                $reflectionMethod = $reflectionClass->getMethod($methodName);
                $reflectionMethod->setAccessible(true);

                return $reflectionMethod;
            }

            if (!$reflectionClass = $reflectionClass->getParentClass()) {
                // This will throw an exception
                new \ReflectionMethod($class, $methodName);
            }
        }
    }

    private static function createAccessiblePropertyReflection($objectOrClass, string $propertyName): \ReflectionProperty
    {
        $class = \is_object($objectOrClass) ? \get_class($objectOrClass) : $objectOrClass;

        $reflectionClass = new \ReflectionClass($class);

        while (true) {
            if ($reflectionClass->hasProperty($propertyName)) {
                $reflectionProperty = $reflectionClass->getProperty($propertyName);
                $reflectionProperty->setAccessible(true);

                return $reflectionProperty;
            }

            if (!$reflectionClass = $reflectionClass->getParentClass()) {
                // This will throw an exception
                new \ReflectionProperty($class, $propertyName);
            }
        }
    }
}
