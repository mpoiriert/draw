<?php

namespace Draw\Component\Core\Reflection;

final class ReflectionAccessor
{
    public static function callMethod(object|string $objectOrClass, string $methodName, mixed ...$arguments)
    {
        $methodReflection = self::createAccessibleMethodReflection($objectOrClass, $methodName);

        $object = $methodReflection->isStatic() ? null : $objectOrClass;

        return $methodReflection->invoke($object, ...$arguments);
    }

    public static function getPropertyValue(object|string $objectOrClass, string $propertyName)
    {
        $property = self::getPropertyReflection($objectOrClass, $propertyName);

        return $property->isStatic()
            ? $property->getValue()
            : $property->getValue($objectOrClass);
    }

    public static function setPropertyValue(object|string $objectOrClass, string $propertyName, mixed $value): void
    {
        $property = self::getPropertyReflection($objectOrClass, $propertyName);

        $property->isStatic()
            ? $property->setValue($value)
            : $property->setValue($objectOrClass, $value);
    }

    /**
     * @param array<string,mixed> $map
     */
    public static function setPropertiesValue(object|string $objectOrClass, array $map): void
    {
        foreach ($map as $property => $value) {
            self::setPropertyValue($objectOrClass, $property, $value);
        }
    }

    private static function createAccessibleMethodReflection($objectOrClass, string $methodName): \ReflectionMethod
    {
        $class = \is_object($objectOrClass) ? $objectOrClass::class : $objectOrClass;

        $reflectionClass = new \ReflectionClass($class);

        while (true) {
            if ($reflectionClass->hasMethod($methodName)) {
                return $reflectionClass->getMethod($methodName);
            }

            if (!$reflectionClass = $reflectionClass->getParentClass()) {
                // This will throw an exception
                new \ReflectionMethod($class, $methodName);
            }
        }
    }

    public static function getPropertyReflection($objectOrClass, string $propertyName): \ReflectionProperty
    {
        $class = \is_object($objectOrClass) ? $objectOrClass::class : $objectOrClass;

        $reflectionClass = new \ReflectionClass($class);

        while (true) {
            if ($reflectionClass->hasProperty($propertyName)) {
                return $reflectionClass->getProperty($propertyName);
            }

            if (!$reflectionClass = $reflectionClass->getParentClass()) {
                // This will throw an exception
                new \ReflectionProperty($class, $propertyName);
            }
        }
    }
}
