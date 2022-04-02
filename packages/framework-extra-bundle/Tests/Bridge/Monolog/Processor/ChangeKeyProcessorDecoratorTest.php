<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\Bridge\Monolog\Processor;

use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\ChangeKeyProcessorDecorator;
use Draw\Bundle\TesterBundle\Tests\TestCase;
use InvalidArgumentException;
use Monolog\Processor\ProcessorInterface;
use stdClass;
use Symfony\Contracts\Service\ResetInterface;

class ChangeKeyProcessorDecoratorTest extends TestCase
{
    private ChangeKeyProcessorDecorator $service;

    private ProcessorInterface $processor;

    private string $key;

    public function setUp(): void
    {
        $this->service = new ChangeKeyProcessorDecorator(
            $this->processor = $this->createMock(ProcessorInterface::class),
            $this->key = uniqid()
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            ResetInterface::class,
            $this->service
        );
    }

    public function testConstructInvalidDecoratedProcessor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument [$decoratedProcessor] is invalid. It must implement method [__invoke]');

        new ChangeKeyProcessorDecorator(
            new stdClass(),
            uniqid()
        );
    }

    public function testInvokeNoExtraKey(): void
    {
        $this->processor->expects($this->once())
            ->method('__invoke')
            ->with([])
            ->willReturn([]);

        $records = [uniqid() => uniqid()];

        $this->assertSame(
            $records,
            $this->service->__invoke($records)
        );
    }

    public function testInvokeWithExtraKey(): void
    {
        $this->processor->expects($this->once())
            ->method('__invoke')
            ->with([])
            ->willReturn(
                ['extra' => ['original_key' => 'value']]
            );

        $records = [uniqid() => uniqid()];

        $this->assertSame(
            $records + ['extra' => [$this->key => 'value']],
            $this->service->__invoke($records)
        );
    }

    public function testReset(): void
    {
        $object = new class() implements ResetInterface {
            public bool $called = false;

            public function reset()
            {
                $this->called = true;
            }

            public function __invoke(): void
            {
            }
        };

        $service = new ChangeKeyProcessorDecorator(
            $object,
            uniqid()
        );

        $service->reset();

        $this->assertTrue($object->called);
    }

    public function testResetDoesNotImplement(): void
    {
        $object = new class() {
            public bool $called = false;

            public function reset()
            {
                $this->called = true;
            }

            public function __invoke(): void
            {
            }
        };

        $service = new ChangeKeyProcessorDecorator(
            $object,
            uniqid()
        );

        $service->reset();

        $this->assertFalse($object->called);
    }
}
