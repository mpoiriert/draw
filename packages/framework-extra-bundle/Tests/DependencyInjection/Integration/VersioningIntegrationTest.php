<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\VersioningIntegration;
use Draw\Component\Application\Versioning\Command\UpdateDeployedVersionCommand;
use Draw\Component\Application\Versioning\EventListener\FetchRunningVersionListener;
use Draw\Component\Application\Versioning\VersionManager;
use Draw\Contracts\Application\VersionVerificationInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;

/**
 * @covers \Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\VersioningIntegration
 *
 * @property VersioningIntegration $integration
 */
class VersioningIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new VersioningIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'versioning';
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
                    'draw.versioning.command.update_deployed_version_command',
                    [
                        UpdateDeployedVersionCommand::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.versioning.version_manager',
                    [
                        VersionManager::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.versioning.event_listener.fetch_running_version_listener',
                    [
                        FetchRunningVersionListener::class,
                    ],
                    function (Definition $definition) {
                        $parameter = $definition->getArgument('$projectDirectory');

                        static::assertInstanceOf(Parameter::class, $parameter);
                        static::assertSame('kernel.project_dir', (string) $parameter);
                    }
                ),
            ],
            [
                VersionManager::class => [
                    VersionVerificationInterface::class,
                ],
            ],
        ];
    }
}
