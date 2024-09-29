<?php

namespace Draw\Component\Tester\Test\DependencyInjection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

abstract class ConfigurationTestCase extends TestCase
{
    private ConfigurationInterface $configuration;

    abstract public function createConfiguration(): ConfigurationInterface;

    abstract public function getDefaultConfiguration(): array;

    abstract public static function provideTestInvalidConfiguration(): iterable;

    protected function setUp(): void
    {
        $this->configuration = $this->createConfiguration();
    }

    #[DataProvider('provideTestInvalidConfiguration')]
    public function testInvalidConfiguration(array $configuration, string $expectedMessage): void
    {
        $this->expectException(InvalidConfigurationException::class);

        try {
            $this->processConfiguration([$configuration]);
        } catch (InvalidConfigurationException $error) {
            $replaces = [
                'at path' => 'under',
                'child node' => 'child config',
                '"' => '',
                'boolean' => 'bool',
            ];
            static::assertSame(
                str_replace(array_keys($replaces), array_values($replaces), $expectedMessage),
                str_replace(array_keys($replaces), array_values($replaces), $error->getMessage()),
            );
            throw $error;
        }
    }

    public function testDefault(): void
    {
        $config = $this->processConfiguration([[]]);

        static::assertEqualsCanonicalizing(
            $this->getDefaultConfiguration(),
            $config
        );
    }

    protected function processConfiguration(array $configs): array
    {
        return (new Processor())->processConfiguration($this->configuration, $configs);
    }
}
