<?php

namespace Draw\Component\Log\Tests\DependencyInjection;

use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\Test\IntegrationTestCase;
use Draw\Component\DependencyInjection\Integration\Test\ServiceConfiguration;
use Draw\Component\Log\DependencyInjection\LogIntegration;
use Draw\Component\Log\Monolog\Processor\DelayProcessor;
use Draw\Component\Log\Symfony\EventListener\SlowRequestLoggerListener;
use Draw\Component\Log\Symfony\Processor\RequestHeadersProcessor;
use Draw\Component\Log\Symfony\Processor\TokenProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bridge\Monolog\Processor\ConsoleCommandProcessor;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\RequestMatcher\HostRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\IpsRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\PathRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\PortRequestMatcher;

/**
 * @property LogIntegration $integration
 *
 * @internal
 */
#[CoversClass(LogIntegration::class)]
class LogIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new LogIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'log';
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'slow_request' => [
                'enabled' => false,
                'default_duration' => 10000,
                'request_matchers' => [],
            ],
            'enable_all_processors' => false,
            'processor' => [
                'console_command' => [
                    'enabled' => false,
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
        ];
    }

    public static function provideLoadCases(): iterable
    {
        yield 'all' => [
            [
                [
                    'enable_all_processors' => true,
                ],
            ],
            [
                new ServiceConfiguration(
                    'draw.log.console_command_processor',
                    [
                        ConsoleCommandProcessor::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.log.delay_processor',
                    [
                        DelayProcessor::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.log.request_headers_processor',
                    [
                        RequestHeadersProcessor::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.log.token_processor',
                    [
                        TokenProcessor::class,
                    ]
                ),
            ],
        ];

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
                    'draw.log.slow_request_logger_listener',
                    [
                        SlowRequestLoggerListener::class,
                    ],
                    static function (Definition $definition): void {
                        $chainRequestMatcherDefinitions = $definition->getArgument('$requestMatchers');

                        static::assertCount(2, $chainRequestMatcherDefinitions);

                        $chainRequestMatcherDefinitions5000 = $chainRequestMatcherDefinitions[5000];

                        static::assertCount(1, $chainRequestMatcherDefinitions5000);

                        $chainRequestMatcherDefinition = $chainRequestMatcherDefinitions5000[0];

                        static::assertInstanceOf(Definition::class, $chainRequestMatcherDefinition);

                        self::assertRequestMatcherDefinitions(
                            $chainRequestMatcherDefinition->getArgument('$matchers'),
                            [
                                [HostRequestMatcher::class, ['example.com']],
                                [IpsRequestMatcher::class, [['127.0.0.1']]],
                                [MethodRequestMatcher::class, [['GET', 'POST']]],
                                [PathRequestMatcher::class, ['^/api']],
                                [PortRequestMatcher::class, [80]],
                            ]
                        );

                        $chainRequestMatcherDefinitions9000 = $chainRequestMatcherDefinitions[9000];

                        static::assertCount(1, $chainRequestMatcherDefinitions9000);

                        $chainRequestMatcherDefinition = $chainRequestMatcherDefinitions9000[0];

                        static::assertInstanceOf(Definition::class, $chainRequestMatcherDefinition);

                        self::assertRequestMatcherDefinitions(
                            $chainRequestMatcherDefinition->getArgument('$matchers'),
                            [
                                [PathRequestMatcher::class, ['^/admin']],
                            ]
                        );
                    }
                ),
            ],
        ];
    }

    /**
     * @param array<Definition> $definitions
     */
    private static function assertRequestMatcherDefinitions(
        array $definitions,
        array $expectedDefinitions,
    ): void {
        static::assertCount(
            \count($expectedDefinitions),
            $definitions
        );

        foreach ($definitions as $index => $definition) {
            static::assertInstanceOf(Definition::class, $definition);

            [$expectedClass, $expectedArguments] = $expectedDefinitions[$index];

            static::assertSame(
                $expectedClass,
                $definition->getClass()
            );

            static::assertSame(
                $expectedArguments,
                $definition->getArguments()
            );
        }
    }
}
