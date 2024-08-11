<?php

namespace Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AutowireMock implements AutowireInterface
{
    public static function getPriority(): int
    {
        return 255;
    }

    public function autowire(TestCase $testCase, \ReflectionProperty $reflectionProperty): void
    {
        $propertyName = $reflectionProperty->getName();
        $type = $reflectionProperty->getType();

        if (!$type instanceof \ReflectionIntersectionType) {
            throw new \RuntimeException('Property '.$propertyName.' of class '.$testCase::class.' must have a type hint intersection with Mock.');
        }

        $types = $type->getTypes();

        if (2 !== \count($types)) {
            throw new \RuntimeException('Property '.$propertyName.' of class '.$testCase::class.' can only have 2 intersection types.');
        }

        foreach ($types as $type) {
            if (!$type instanceof \ReflectionNamedType) {
                throw new \RuntimeException('Property '.$propertyName.' of class '.$testCase::class.' intersection must be of named type.');
            }

            if (MockObject::class === $type->getName()) {
                continue;
            }

            $reflectionProperty->setValue(
                $testCase,
                ReflectionAccessor::callMethod($testCase, 'createMock', $type->getName())
            );

            return;
        }
    }
}
