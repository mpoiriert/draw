<?php

namespace Draw\Component\Tester\Tests\DependencyInjection;

use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\Test\IntegrationTestCase;
use Draw\Component\DependencyInjection\Integration\Test\ServiceConfiguration;
use Draw\Component\Tester\Command\TestsCoverageCheckCommand;
use Draw\Component\Tester\DependencyInjection\TesterIntegration;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @property TesterIntegration $integration
 *
 * @internal
 */
#[CoversClass(TesterIntegration::class)]
class TesterIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new TesterIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'tester';
    }

    public function getDefaultConfiguration(): array
    {
        return [];
    }

    public static function provideLoadCases(): iterable
    {
        yield [
            [],
            [
                new ServiceConfiguration(
                    'draw.tester.command.tests_coverage_check_command',
                    [
                        TestsCoverageCheckCommand::class,
                    ]
                ),
            ],
        ];
    }
}
