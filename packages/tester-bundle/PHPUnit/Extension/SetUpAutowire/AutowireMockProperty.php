<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use PHPUnit\Framework\TestCase;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class AutowireMockProperty implements AutowireInterface
{
    public static function getPriority(): int
    {
        return -100;
    }

    public function __construct(private string $property, private ?string $fromProperty = null)
    {
        $this->fromProperty ??= $property;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getFromProperty(): string
    {
        return $this->fromProperty;
    }

    public function autowire(TestCase $testCase, \ReflectionProperty $reflectionProperty): void
    {
        $object = $reflectionProperty->getValue($testCase);

        ReflectionAccessor::setPropertyValue(
            $object,
            $this->getProperty(),
            ReflectionAccessor::getPropertyValue(
                $testCase,
                $this->getFromProperty()
            )
        );
    }
}
