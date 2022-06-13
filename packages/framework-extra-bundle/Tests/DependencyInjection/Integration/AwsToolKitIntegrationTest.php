<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\AwsToolKitIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\AwsToolKit\Command\CloudWatchLogsDownloadCommand;
use Draw\Component\AwsToolKit\EventListener\NewestInstanceRoleCheckListener;
use Draw\Component\AwsToolKit\Imds\ImdsClientInterface;
use Draw\Component\AwsToolKit\Imds\ImdsClientV1;
use Draw\Component\AwsToolKit\Imds\ImdsClientV2;

/**
 * @covers \Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\AwsToolKitIntegration
 *
 * @property AwsToolKitIntegration $integration
 */
class AwsToolKitIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new AwsToolKitIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'aws_tool_kit';
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'imds_version' => null,
            'newest_instance_role_check' => [
                'enabled' => false,
            ],
        ];
    }

    public function provideTestLoad(): iterable
    {
        yield 'imds_version_1' => [
            [
                [
                    'imds_version' => 1,
                    'newest_instance_role_check' => [
                        'enabled' => true,
                    ],
                ],
            ],
            [
                new ServiceConfiguration(
                    'draw.aws_tool_kit.command.cloud_watch_logs_download_command',
                    [
                        CloudWatchLogsDownloadCommand::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.aws_tool_kit.event_listener.newest_instance_role_check_listener',
                    [
                        NewestInstanceRoleCheckListener::class,
                    ],
                ),
                new ServiceConfiguration(
                    'draw.aws_tool_kit.imds.imds_client_v1',
                    [
                        ImdsClientV1::class,
                    ],
                ),
            ],
            [
                ImdsClientV1::class => [
                    ImdsClientInterface::class,
                ],
            ],
        ];

        yield 'imds_version_2' => [
            [
                [
                    'imds_version' => 2,
                ],
            ],
            [
                new ServiceConfiguration(
                    'draw.aws_tool_kit.command.cloud_watch_logs_download_command',
                    [
                        CloudWatchLogsDownloadCommand::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.aws_tool_kit.imds.imds_client_v2',
                    [
                        ImdsClientV2::class,
                    ],
                ),
            ],
            [
                ImdsClientV2::class => [
                    ImdsClientInterface::class,
                ],
            ],
        ];
    }
}
