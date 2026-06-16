<?php

namespace Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AutowireDouble implements OptionAwareAutowireInterface
{
    public function __construct(
        private array $options = [],
    ) {
    }

    public static function getPriority(): int
    {
        return 255;
    }

    public function autowire(TestCase $testCase, \ReflectionProperty $reflectionProperty): void
    {
        $propertyName = $reflectionProperty->getName();
        $reflectionPropertyType = $reflectionProperty->getType();

        if (!$reflectionPropertyType instanceof \ReflectionIntersectionType) {
            throw new \RuntimeException('Property '.$propertyName.' (class '.$testCase::class.') must have a type hint intersection with '.MockObject::class.' or '.Stub::class.'.');
        }

        if (2 !== \count($reflectionPropertyTypes = $reflectionPropertyType->getTypes())) {
            throw new \RuntimeException('Property '.$propertyName.' (class '.$testCase::class.') must have a type hint intersection with '.MockObject::class.' or '.Stub::class.'.');
        }

        $targetType = null;
        $doubleType = null;

        /** @var \ReflectionNamedType $reflectionPropertyType */
        foreach ($reflectionPropertyTypes as $reflectionPropertyType) {
            if (\in_array($reflectionPropertyType->getName(), [MockObject::class, Stub::class], true)) {
                $doubleType = $reflectionPropertyType;
            } else {
                $targetType = $reflectionPropertyType;
            }
        }

        if (!$targetType instanceof \ReflectionNamedType) {
            throw new \RuntimeException('Target type of property '.$propertyName.' (class '.$testCase::class.') could not be determined.');
        }

        if (!$doubleType instanceof \ReflectionNamedType) {
            throw new \RuntimeException('Double type of property '.$propertyName.' (class '.$testCase::class.') could not be determined.');
        }

        $reflectionProperty->setValue(
            $testCase,
            ReflectionAccessor::callMethod(
                $testCase,
                MockObject::class === $doubleType->getName() || ($this->options['asMock'] ?? false)
                    ? 'createMock'
                    : 'createStub',
                $targetType->getName()
            )
        );
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
