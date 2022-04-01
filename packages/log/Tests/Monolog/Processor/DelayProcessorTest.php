<?php

namespace Draw\Component\Log\Tests\Monolog\Processor;

use Draw\Component\Log\Monolog\Processor\DelayProcessor;
use PHPUnit\Framework\TestCase;

class DelayProcessorTest extends TestCase
{
    private DelayProcessor $delayProcessor;

    private string $key;

    public function setUp(): void
    {
        $this->delayProcessor = new DelayProcessor($this->key = uniqid());
    }

    public function testInvoke(): void
    {
        $this->assertSame(
            [
                'extra' => [
                    $this->key => number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2),
                ],
            ],
            $this->delayProcessor->__invoke([])
        );
    }

    public function testReset(): void
    {
        $this->delayProcessor->reset();
        $this->assertSame(
            [
                'extra' => [
                    $this->key => '0.00',
                ],
            ],
            $this->delayProcessor->__invoke([])
        );
    }

    public function testInvokeDefaultKey(): void
    {
        $this->delayProcessor = new DelayProcessor();
        $this->assertArrayHasKey(
            'delay',
            $this->delayProcessor->__invoke([])['extra']
        );
    }
}
