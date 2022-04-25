<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\AwsToolKit\Command\CloudWatchLogsDownloadCommand;
use Draw\Component\AwsToolKit\Imds\ImdsClientInterface;
use Draw\Component\AwsToolKit\Listener\NewestInstanceRoleCheckListener;

class DrawFrameworkExtraExtensionAwsToolKitEnabledTest extends DrawFrameworkExtraExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['aws_tool_kit'] = [
            'enabled' => true,
            'imds_version' => 1,
            'newest_instance_role_check' => [
                'enabled' => true,
            ],
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.aws_tool_kit.command.cloud_watch_logs_download'];
        yield [CloudWatchLogsDownloadCommand::class, 'draw.aws_tool_kit.command.cloud_watch_logs_download'];
        yield ['draw.aws_tool_kit.newest_instance_role_check_listener'];
        yield [NewestInstanceRoleCheckListener::class, 'draw.aws_tool_kit.newest_instance_role_check_listener'];
        yield ['draw.aws_tool_kit.imds_client_v1'];
        yield [ImdsClientInterface::class, 'draw.aws_tool_kit.imds_client_v1'];
    }
}
