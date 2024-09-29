<?php

namespace Draw\Component\Log\Tests\Monolog\Processor;

use Draw\Component\Log\Monolog\Processor\DelayProcessor;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class DelayProcessorTest extends TestCase
{
    private DelayProcessor $delayProcessor;

    private string $key;

    protected function setUp(): void
    {
        $this->delayProcessor = new DelayProcessor($this->key = uniqid());
    }

    public function testInvoke(): void
    {
        static::assertSame(
            [
                $this->key => number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2),
            ],
            $this->delayProcessor->__invoke(
                new LogRecord(
                    new \DateTimeImmutable(),
                    'test',
                    Level::Info,
                    'message',
                )
            )->toArray()['extra']
        );
    }

    public function testReset(): void
    {
        $this->delayProcessor->reset();
        static::assertSame(
            [$this->key => '0.00'],
            $this->delayProcessor->__invoke(
                new LogRecord(
                    new \DateTimeImmutable(),
                    'test',
                    Level::Info,
                    'message',
                )
            )->toArray()['extra']
        );
    }

    public function testInvokeDefaultKey(): void
    {
        $this->delayProcessor = new DelayProcessor();
        static::assertArrayHasKey(
            'delay',
            $this->delayProcessor->__invoke(
                new LogRecord(
                    new \DateTimeImmutable(),
                    'test',
                    Level::Info,
                    'message',
                )
            )->toArray()['extra']
        );
    }
}
