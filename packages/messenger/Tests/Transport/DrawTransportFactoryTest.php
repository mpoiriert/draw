<?php

namespace Draw\Component\Messenger\Tests\Transport;

use Draw\Component\Messenger\Tests\TestCase;
use Draw\Component\Messenger\Transport\DrawTransport;
use Draw\Component\Messenger\Transport\DrawTransportFactory;
use Symfony\Component\Messenger\Exception\InvalidArgumentException;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

/**
 * @covers \Draw\Component\Messenger\Transport\DrawTransportFactory
 */
class DrawTransportFactoryTest extends TestCase
{
    private DrawTransportFactory $service;

    protected function setUp(): void
    {
        $this->service = new DrawTransportFactory($this);
    }

    public function testCreateTransport(): void
    {
        $transport = $this->service->createTransport(
            'draw://default',
            [],
            new PhpSerializer()
        );

        static::assertInstanceOf(
            DrawTransport::class,
            $transport
        );
    }

    public function testCreateTransportInvalidHost(): void
    {
        $dsn = 'draw://invalid';
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage(sprintf('Could not find Doctrine connection from Messenger DSN "%s".', $dsn));

        $this->service->createTransport(
            $dsn,
            [],
            new PhpSerializer()
        );
    }

    public function testBuildConfigurationInvalidDsn(): void
    {
        $dsn = '&:/@&#test';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('The given Draw Messenger DSN "%s" is invalid.', $dsn));

        $this->service::buildConfiguration($dsn, []);
    }

    public function testBuildConfigurationExtraOptions(): void
    {
        $options = [$key = uniqid('key-') => uniqid()];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Unknown option found : [%s]. Allowed options are [table_name, tag_table_name, queue_name, redeliver_timeout, auto_setup]',
                $key
            )
        );

        $this->service::buildConfiguration('draw://default', $options);
    }

    public function testBuildConfigurationExtraOptionsInQuery(): void
    {
        $dsn = 'draw://default?'.($key = uniqid('key-')).'='.uniqid();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Unknown option found in DSN: [%s]. Allowed options are [table_name, tag_table_name, queue_name, redeliver_timeout, auto_setup]',
                $key
            )
        );

        $this->service::buildConfiguration($dsn, []);
    }

    public function provideTestBuildConfiguration(): iterable
    {
        yield 'default' => [
            'draw://default',
            [],
            [
                'connection' => 'default',
                'table_name' => 'draw_messenger__message',
                'tag_table_name' => 'draw_messenger__message_tag',
                'queue_name' => 'default',
                'redeliver_timeout' => 3600,
                'auto_setup' => false,
            ],
        ];

        yield 'query-queue_name' => [
            'draw://default?queue_name='.($value = uniqid('queue-')),
            [],
            [
                'connection' => 'default',
                'table_name' => 'draw_messenger__message',
                'tag_table_name' => 'draw_messenger__message_tag',
                'queue_name' => $value,
                'redeliver_timeout' => 3600,
                'auto_setup' => false,
            ],
        ];

        yield 'query-table_name' => [
            'draw://default?table_name='.($value = uniqid('table-')),
            [],
            [
                'connection' => 'default',
                'table_name' => $value,
                'tag_table_name' => $value.'_tag',
                'queue_name' => 'default',
                'redeliver_timeout' => 3600,
                'auto_setup' => false,
            ],
        ];

        yield 'query-auto_setup' => [
            'draw://default?auto_setup=true',
            [],
            [
                'connection' => 'default',
                'table_name' => 'draw_messenger__message',
                'tag_table_name' => 'draw_messenger__message_tag',
                'queue_name' => 'default',
                'redeliver_timeout' => 3600,
                'auto_setup' => true,
            ],
        ];

        yield 'query-tag_table_name' => [
            'draw://default?tag_table_name='.($value = uniqid('table-')),
            [],
            [
                'connection' => 'default',
                'table_name' => 'draw_messenger__message',
                'tag_table_name' => $value,
                'queue_name' => 'default',
                'redeliver_timeout' => 3600,
                'auto_setup' => false,
            ],
        ];

        yield 'query-redeliver_timeout' => [
            'draw://default?redeliver_timeout='.($value = random_int(1, \PHP_INT_MAX)),
            [],
            [
                'connection' => 'default',
                'table_name' => 'draw_messenger__message',
                'tag_table_name' => 'draw_messenger__message_tag',
                'queue_name' => 'default',
                'redeliver_timeout' => $value,
                'auto_setup' => false,
            ],
        ];

        yield 'options-queue_name' => [
            'draw://default',
            [
                'queue_name' => ($value = uniqid('queue-')),
            ],
            [
                'connection' => 'default',
                'table_name' => 'draw_messenger__message',
                'tag_table_name' => 'draw_messenger__message_tag',
                'queue_name' => $value,
                'redeliver_timeout' => 3600,
                'auto_setup' => false,
            ],
        ];

        yield 'options-table_name' => [
            'draw://default',
            [
                'table_name' => ($value = uniqid('table-')),
            ],
            [
                'connection' => 'default',
                'table_name' => $value,
                'tag_table_name' => $value.'_tag',
                'queue_name' => 'default',
                'redeliver_timeout' => 3600,
                'auto_setup' => false,
            ],
        ];

        yield 'options-auto_setup' => [
            'draw://default',
            [
                'auto_setup' => true,
            ],
            [
                'connection' => 'default',
                'table_name' => 'draw_messenger__message',
                'tag_table_name' => 'draw_messenger__message_tag',
                'queue_name' => 'default',
                'redeliver_timeout' => 3600,
                'auto_setup' => true,
            ],
        ];

        yield 'options-tag_table_name' => [
            'draw://default',
            [
                'tag_table_name' => ($value = uniqid('table-')),
            ],
            [
                'connection' => 'default',
                'table_name' => 'draw_messenger__message',
                'tag_table_name' => $value,
                'queue_name' => 'default',
                'redeliver_timeout' => 3600,
                'auto_setup' => false,
            ],
        ];

        yield 'options-redeliver_timeout' => [
            'draw://default',
            [
                'redeliver_timeout' => ($value = random_int(1, \PHP_INT_MAX)),
            ],
            [
                'connection' => 'default',
                'table_name' => 'draw_messenger__message',
                'tag_table_name' => 'draw_messenger__message_tag',
                'queue_name' => 'default',
                'redeliver_timeout' => $value,
                'auto_setup' => false,
            ],
        ];
    }

    /**
     * @dataProvider provideTestBuildConfiguration
     */
    public function testBuildConfiguration(string $dsn, array $options, array $expectedResult): void
    {
        $result = $this->service::buildConfiguration($dsn, $options);

        ksort($expectedResult);
        ksort($result);

        static::assertSame(
            $expectedResult,
            $result
        );
    }

    public function provideTestSupports(): iterable
    {
        yield 'draw' => [
            'draw://',
            true,
        ];

        yield 'not-draw' => [
            uniqid().'://',
            false,
        ];

        yield 'draw-not-first-position' => [
            'test-draw://',
            false,
        ];

        yield 'draw-with-something-else' => [
            uniqid('draw').'://',
            false,
        ];
    }

    /**
     * @dataProvider provideTestSupports
     */
    public function testSupports(string $dsn, bool $support): void
    {
        static::assertSame(
            $support,
            $this->service->supports($dsn, [])
        );
    }
}
