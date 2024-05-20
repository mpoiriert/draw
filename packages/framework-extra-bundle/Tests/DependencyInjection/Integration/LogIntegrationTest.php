<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\RequestHeadersProcessor;
use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\TokenProcessor;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\LogIntegration;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\Test\IntegrationTestCase;
use Draw\Component\DependencyInjection\Integration\Test\ServiceConfiguration;
use Draw\Component\Log\Monolog\Processor\DelayProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bridge\Monolog\Processor\ConsoleCommandProcessor;

/**
 * @property LogIntegration $integration
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

    public static function provideTestLoad(): iterable
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
    }
}
