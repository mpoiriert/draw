<?php

declare(strict_types=1);

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

use Draw\Bundle\TesterBundle\WebTestCase as DrawWebTestCase;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowireInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AutowireClient implements AutowireInterface
{
    public static function getPriority(): int
    {
        return 1000;
    }

    public function __construct(
        private array $options = [],
        private array $server = [],
    ) {
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getServer(): array
    {
        return $this->server;
    }

    public function autowire(TestCase $testCase, \ReflectionProperty $reflectionProperty): void
    {
        if (!$testCase instanceof SymfonyWebTestCase && !$testCase instanceof DrawWebTestCase) {
            throw new \RuntimeException(
                sprintf(
                    'AutowireClient attribute can only be used in %s or %s.',
                    SymfonyWebTestCase::class,
                    DrawWebTestCase::class
                )
            );
        }

        // This is to ensure the kernel is not booted before calling createClient
        // Can happen if we use the container in a setUpBeforeClass method or a beforeClass hook
        ReflectionAccessor::callMethod(
            $testCase,
            'ensureKernelShutdown'
        );

        $reflectionProperty->setValue(
            $testCase,
            ReflectionAccessor::callMethod(
                $testCase,
                'createClient',
                $this->getOptions(),
                $this->getServer()
            )
        );
    }
}
