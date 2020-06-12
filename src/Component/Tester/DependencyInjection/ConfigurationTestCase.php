<?php

namespace Draw\Component\Tester\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

abstract class ConfigurationTestCase extends TestCase
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    abstract public function createConfiguration(): ConfigurationInterface;

    abstract public function getDefaultConfiguration(): array;

    abstract public function provideTestInvalidConfiguration(): iterable;

    public function setUp(): void
    {
        $this->configuration = $this->createConfiguration();
    }

    /**
     * @dataProvider provideTestInvalidConfiguration
     */
    public function testInvalidConfiguration(array $configuration, string $expectedMessage)
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->processConfiguration([$configuration]);
    }

    public function testDefault()
    {
        $config = $this->processConfiguration([[]]);

        $this->assertEquals(
            $this->getDefaultConfiguration(),
            $config
        );
    }

    protected function processConfiguration(array $configs): array
    {
        return (new Processor())->processConfiguration($this->configuration, $configs);
    }
}
