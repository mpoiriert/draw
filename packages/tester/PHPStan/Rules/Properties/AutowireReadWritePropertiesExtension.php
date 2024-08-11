<?php

namespace Draw\Component\Tester\PHPStan\Rules\Properties;

use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;

class AutowireReadWritePropertiesExtension implements ReadWritePropertiesExtension
{
    public function __construct(
        private string $attribute
    ) {
    }

    public function isAlwaysRead(PropertyReflection $property, string $propertyName): bool
    {
        return false;
    }

    public function isAlwaysWritten(PropertyReflection $property, string $propertyName): bool
    {
        return $this->hasProperAttribute($property, $propertyName);
    }

    public function isInitialized(PropertyReflection $property, string $propertyName): bool
    {
        return $this->hasProperAttribute($property, $propertyName);
    }

    private function hasProperAttribute(PropertyReflection $property, string $propertyName): bool
    {
        $properReflection = $property->getDeclaringClass()->getNativeProperty($propertyName)->getNativeReflection();

        return 0 !== \count($properReflection->getAttributes($this->attribute, \ReflectionAttribute::IS_INSTANCEOF));
    }
}
