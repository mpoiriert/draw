<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ConsoleIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ProcessIntegration;
use Draw\Component\Process\ProcessFactory;
use Draw\Contracts\Process\ProcessFactoryInterface;

/**
 * @covers \Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ProcessIntegration
 *
 * @property ConsoleIntegration $integration
 */
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

    public function provideTestLoad(): iterable
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
