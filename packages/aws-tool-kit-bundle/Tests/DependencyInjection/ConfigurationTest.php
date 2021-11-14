<?php

namespace Draw\Bundle\AwsToolKitBundle\Tests\DependencyInjection;

use Draw\Bundle\AwsToolKitBundle\DependencyInjection\Configuration;
use Draw\Component\Tester\DependencyInjection\ConfigurationTestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigurationTest extends ConfigurationTestCase
{
    public function createConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'imds_version' => 1,
        ];
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['imds_version' => 3],
            'The value 3 is not allowed for path "draw_aws_tool_kit.imds_version". Permissible values: 1, 2, null',
        ];
    }
}
