<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

use Draw\Component\Core\Reflection\ReflectionExtractor;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowireInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AutowireService implements AutowireInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function __construct(private ?string $serviceId = null)
    {
    }

    public function getServiceId(): ?string
    {
        return $this->serviceId;
    }

    public function autowire(TestCase $testCase, \ReflectionProperty $reflectionProperty): void
    {
        \assert($testCase instanceof KernelTestCase);

        $serviceId = $this->serviceId;

        if (null === $serviceId) {
            $classes = ReflectionExtractor::getClasses($reflectionProperty->getType());

            if (1 !== \count($classes)) {
                throw new \RuntimeException('Property '.$reflectionProperty->getName().' of class '.$testCase::class.' must have a type hint.');
            }

            $serviceId = $classes[0];
        }

        $container = (new \ReflectionMethod($testCase, 'getContainer'))->invoke($testCase);

        $reflectionProperty->setValue(
            $testCase,
            $container->get($serviceId)
        );
    }
}
