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
            'log' => [
                'enabled' => false,
                'enable_all_processors' => false,
                'processor' => [
                    'console_command' => [
                        'enabled' => false,
                        'key' => 'command',
                        'includeArguments' => true,
                        'includeOptions' => false,
                    ],
                    'delay' => [
                        'enabled' => false,
                        'key' => 'delay',
                    ],
                    'request_headers' => [
                        'enabled' => false,
                        'key' => 'request_headers',
                        'onlyHeaders' => [],
                        'ignoreHeaders' => [],
                    ],
                    'token' => [
                        'enabled' => false,
                        'key' => 'token',
                    ],
                ],
            ],
            'logger' => [
                'enabled' => false,
                'slow_request' => [
                    'enabled' => false,
                    'default_duration' => 10000,
                    'request_matchers' => [],
                ],
            ],
            'messenger' => [
                'enabled' => true,
                'async_routing_configuration' => [
                    'enabled' => false,
                ],
            ],
            'process' => [
                'enabled' => true,
            ],
            'tester' => [
                'enabled' => true,
            ],
        ];
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['invalid' => true],
            'Unrecognized option invalid under draw_framework_extra. Available options are log, logger, messenger, process, tester.',
        ];
    }
}
