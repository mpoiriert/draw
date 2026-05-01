<?php

namespace Draw\Bundle\SonataImportBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataImportBundle\DependencyInjection\Configuration;
use Draw\Bundle\SonataImportBundle\Import\Importer;
use Draw\Component\Tester\Test\DependencyInjection\ConfigurationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @internal
 */
#[CoversClass(Configuration::class)]
class ConfigurationTest extends ConfigurationTestCase
{
    public function createConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'classes' => [],
            'skip_value' => Importer::DEFAULT_SKIP_VALUE,
        ];
    }

    public function testCustomSkipValue(): void
    {
        $config = $this->processConfiguration([['skip_value' => '*SKIP*']]);

        static::assertSame('*SKIP*', $config['skip_value']);
    }

    public static function provideInvalidConfigurationCases(): iterable
    {
        yield 'empty skip_value' => [
            ['skip_value' => ''],
            \sprintf(
                "The path \"draw_sonata_import.skip_value\" cannot contain an empty value, but got \"\".\nHint: %s",
                'Sentinel value that, when present in a CSV cell, preserves the existing value on the entity for that (row, column) pair instead of overwriting it. The check runs before any type coercion (date, etc.).'
            ),
        ];
    }
}
