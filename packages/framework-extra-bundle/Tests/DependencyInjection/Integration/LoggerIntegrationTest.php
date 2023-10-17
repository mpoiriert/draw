<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\LoggerIntegration;
use Draw\Bundle\FrameworkExtraBundle\Logger\EventListener\SlowRequestLoggerListener;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @property LoggerIntegration $integration
 */
#[CoversClass(LoggerIntegration::class)]
class LoggerIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new LoggerIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'logger';
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'slow_request' => [
                'enabled' => false,
                'default_duration' => 10000,
                'request_matchers' => [],
            ],
        ];
    }

    public static function provideTestLoad(): iterable
    {
        yield 'default' => [];

        yield 'slow_request' => [
            [
                [
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
            ],
            [
                new ServiceConfiguration(
                    'draw.logger.event_listener.slow_request_logger_listener',
                    [
                        SlowRequestLoggerListener::class,
                    ],
                    function (Definition $definition): void {
                        $requestMatcherDefinitions = $definition->getArgument('$requestMatchers');

                        static::assertCount(2, $requestMatcherDefinitions);

                        $requestMatcherDefinitions5000 = $requestMatcherDefinitions[5000];

                        static::assertCount(1, $requestMatcherDefinitions5000);

                        $requestMatcherDefinition = $requestMatcherDefinitions5000[0];

                        static::assertInstanceOf(Definition::class, $requestMatcherDefinition);

                        static::assertSame(
                            [
                                '$ips' => [
                                    '127.0.0.1',
                                ],
                                '$path' => '^/api',
                                '$host' => 'example.com',
                                '$port' => 80,
                                '$methods' => [
                                    'GET',
                                    'POST',
                                ],
                                '$schemes' => [],
                            ],
                            $requestMatcherDefinition->getArguments()
                        );

                        $requestMatcherDefinitions9000 = $requestMatcherDefinitions[9000];

                        static::assertCount(1, $requestMatcherDefinitions9000);

                        $requestMatcherDefinition = $requestMatcherDefinitions9000[0];

                        static::assertInstanceOf(Definition::class, $requestMatcherDefinition);

                        static::assertSame(
                            [
                                '$path' => '^/admin',
                                '$host' => null,
                                '$port' => null,
                                '$schemes' => [],
                                '$ips' => [],
                                '$methods' => [],
                            ],
                            $requestMatcherDefinition->getArguments()
                        );
                    }
                ),
            ],
        ];
    }
}
