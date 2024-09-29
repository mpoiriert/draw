<?php

namespace Draw\Component\Process\Tests\DependencyInjection;

use Draw\Component\Console\DependencyInjection\ConsoleIntegration;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\Test\IntegrationTestCase;
use Draw\Component\DependencyInjection\Integration\Test\ServiceConfiguration;
use Draw\Component\Process\DependencyInjection\ProcessIntegration;
use Draw\Component\Process\ProcessFactory;
use Draw\Contracts\Process\ProcessFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @property ConsoleIntegration $integration
 *
 * @internal
 */
#[CoversClass(ProcessIntegration::class)]
class ProcessIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new ProcessIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'process';
    }

    public function getDefaultConfiguration(): array
    {
        return [];
    }

    public static function provideTestLoad(): iterable
    {
        yield [
            [],
            [
                new ServiceConfiguration(
                    'draw.process.process_factory',
                    [
                        ProcessFactory::class,
                    ]
                ),
            ],
            [
                ProcessFactory::class => [
                    ProcessFactoryInterface::class,
                ],
            ],
        ];
    }
}
