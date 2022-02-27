<?php

namespace Draw\Bundle\AwsToolKitBundle\Tests\DependencyInjection;

use Draw\Bundle\AwsToolKitBundle\Imds\ImdsClientInterface;

/**
 * @covers \Draw\Bundle\AwsToolKitBundle\DependencyInjection\DrawAwsToolKitExtension
 */
class DrawAwsToolKitExtensionImdsV2Test extends DrawAwsToolKitExtensionTest
{
    public function getConfiguration(): array
    {
        return ['imds_version' => 2];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.aws_tool_kit.imds_client_v2'];
        yield [ImdsClientInterface::class, 'draw.aws_tool_kit.imds_client_v2'];
    }
}
