<?php

namespace Draw\Bundle\TesterBundle\PHPStan\Rules\Properties;

use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;

class AutowireReadWritePropertiesExtension implements ReadWritePropertiesExtension
{
    public function isAlwaysRead(PropertyReflection $property, string $propertyName): bool
    {
        return false;
    }

    public function isAlwaysWritten(PropertyReflection $property, string $propertyName): bool
    {
        return $this->hasProperAttribute($property, $propertyName, AutowireService::class);
    }

    public function isInitialized(PropertyReflection $property, string $propertyName): bool
    {
        return $this->hasProperAttribute($property, $propertyName, AutowireService::class);
    }

    private function hasProperAttribute(PropertyReflection $property, string $propertyName, string $attribute): bool
    {
        $properReflection = $property->getDeclaringClass()->getNativeProperty($propertyName)->getNativeReflection();

        return 0 !== \count($properReflection->getAttributes($attribute, \ReflectionAttribute::IS_INSTANCEOF));
    }
}
