<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\LoggerIntegration;
use Draw\Bundle\FrameworkExtraBundle\Logger\EventListener\SlowRequestLoggerListener;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\RequestMatcher\HostRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\IpsRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\PathRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\PortRequestMatcher;

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
