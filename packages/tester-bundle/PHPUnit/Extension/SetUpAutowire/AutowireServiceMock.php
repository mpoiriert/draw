<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowireMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AutowireServiceMock extends AutowireMock
{
    use KernelTestCaseAutowireDependentTrait;

    public static function getPriority(): int
    {
        return 0;
    }

    public function __construct(private ?string $serviceId = null)
    {
    }

    public function autowire(TestCase $testCase, \ReflectionProperty $reflectionProperty): void
    {
        parent::autowire($testCase, $reflectionProperty);

        $value = $reflectionProperty->getValue($testCase);

        $this->getContainer($testCase)->set(
            $this->getServiceId($reflectionProperty),
            $value
        );
    }

    private function getServiceId(\ReflectionProperty $reflectionProperty): string
    {
        $serviceId = $this->serviceId;

        if ($serviceId) {
            return $serviceId;
        }

        $type = $reflectionProperty->getType();

        \assert($type instanceof \ReflectionIntersectionType);

        foreach ($type->getTypes() as $type) {
            \assert($type instanceof \ReflectionNamedType);

            if (MockObject::class === $type->getName()) {
                continue;
            }

            return $type->getName();
        }

        throw new \RuntimeException('Cannot load service id from property '.$reflectionProperty->getName().' of class '.$reflectionProperty->getDeclaringClass()->getName());
    }
}
