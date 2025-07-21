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

    protected function setUp(): void
    {
        $this->configuration = $this->createConfiguration();
    }

    #[DataProvider('provideInvalidConfigurationCases')]
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

    abstract public static function provideInvalidConfigurationCases(): iterable;

    public function testDefault(): void
    {
        static::assertJsonStringEqualsJsonString(
            json_encode($this->getDefaultConfiguration()),
            json_encode($this->processConfiguration([[]]))
        );
    }

    protected function processConfiguration(array $configs): array
    {
        return (new Processor())->processConfiguration($this->configuration, $configs);
    }
}
