<?php

namespace Draw\Bundle\AwsToolKitBundle\Tests\DependencyInjection;

use Draw\Bundle\AwsToolKitBundle\Command\CloudWatchLogsDownloadCommand;
use Draw\Bundle\AwsToolKitBundle\DependencyInjection\DrawAwsToolKitExtension;
use Draw\Bundle\AwsToolKitBundle\Imds\ImdsClientInterface;
use Draw\Bundle\AwsToolKitBundle\Imds\ImdsClientV1;
use Draw\Bundle\AwsToolKitBundle\Imds\ImdsClientV2;
use Draw\Bundle\AwsToolKitBundle\Listener\NewestInstanceRoleListener;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

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
        yield [CloudWatchLogsDownloadCommand::class];
        yield [ImdsClientV1::class];
        yield [ImdsClientV2::class];
        yield [ImdsClientInterface::class, ImdsClientV1::class];
        yield [NewestInstanceRoleListener::class];
    }
}
