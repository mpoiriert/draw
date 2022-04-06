<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

class DrawFrameworkExtraExtensionLoggerTest extends DrawFrameworkExtraExtensionTest
{
    public function getConfiguration(): array
    {
        return [
            'logger' => [
                'enabled' => true,
                'slow_request' => [
                    'request_matchers' => [
                        [
                            'duration' => 5000,
                            'ips' => ['127.0.0.1'],
                            'path' => '^/api',
                            'host' => 'example.com',
                            'port' => 80,
                            'methods' => ['GET', 'POST'],
                        ],
                        [
                            'duration' => 9000,
                            'path' => '^/admin',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.logger.slow_request_logger'];
    }
}
