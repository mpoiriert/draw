<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AutowireParameter implements AutowireInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function __construct(private string $parameter)
    {
    }

    public function getParameter(): string
    {
        return $this->parameter;
    }

    public function autowire(TestCase $testCase, \ReflectionProperty $reflectionProperty): void
    {
        \assert($testCase instanceof KernelTestCase);

        $container = (new \ReflectionMethod($testCase, 'getContainer'))->invoke($testCase);

        $reflectionProperty->setValue(
            $testCase,
            $container->get(ParameterBagInterface::class)->resolveValue($this->getParameter())
        );
    }
}
