<?php

namespace Draw\Bundle\AwsToolKitBundle\Tests\DependencyInjection;

use Draw\Bundle\AwsToolKitBundle\Command\CloudWatchLogsDownloadCommand;
use Draw\Bundle\AwsToolKitBundle\DependencyInjection\DrawAwsToolKitExtension;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @covers \Draw\Bundle\AwsToolKitBundle\DependencyInjection\DrawAwsToolKitExtension
 */
class DrawAwsToolKitExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawAwsToolKitExtension();
    }

    public function getConfiguration(): array
    {
        return [];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield ['draw.aws_tool_kit.cloud_watch_logs_download_command'];
        yield [CloudWatchLogsDownloadCommand::class, 'draw.aws_tool_kit.cloud_watch_logs_download_command'];
    }
}
