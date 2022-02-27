<?php

namespace Draw\Bundle\AwsToolKitBundle\Tests\DependencyInjection;

use Draw\Bundle\AwsToolKitBundle\Listener\NewestInstanceRoleCheckListener;

/**
 * @covers \Draw\Bundle\AwsToolKitBundle\DependencyInjection\DrawAwsToolKitExtension
 */
class DrawAwsToolKitExtensionNewestInstanceRoleCheckTest extends DrawAwsToolKitExtensionImdsV1Test
{
    public function getConfiguration(): array
    {
        return parent::getConfiguration()
            + ['newest_instance_role_check' => true];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.aws_tool_kit.newest_instance_role_check_listener'];
        yield [NewestInstanceRoleCheckListener::class, 'draw.aws_tool_kit.newest_instance_role_check_listener'];
    }
}
