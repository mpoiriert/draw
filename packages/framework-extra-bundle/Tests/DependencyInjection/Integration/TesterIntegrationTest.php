<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\TesterIntegration;
use Draw\Component\Tester\Command\TestsCoverageCheckCommand;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @property TesterIntegration $integration
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

    public static function provideTestLoad(): iterable
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
