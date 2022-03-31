<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Configuration;
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
            'messenger' => [
                'enabled' => true,
                'async_routing_configuration' => [
                    'enabled' => false,
                ],
            ],
            'process' => [
                'enabled' => true,
            ],
        ];
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['invalid' => true],
            'Unrecognized option invalid under draw_framework_extra. Available options are messenger, process.',
        ];
    }
}
