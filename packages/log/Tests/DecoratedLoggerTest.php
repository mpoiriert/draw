<?php

namespace Draw\Component\Log\Tests;

use Draw\Component\Log\DecoratedLogger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
class DecoratedLoggerTest extends TestCase
{
    private DecoratedLogger $object;

    private LoggerInterface&MockObject $logger;

    private array $defaultContext;

    private string $decorateMessage;

    protected function setUp(): void
    {
        $this->object = new DecoratedLogger(
            $this->logger = $this->createMock(LoggerInterface::class),
            $this->defaultContext = ['key' => uniqid()],
            $this->decorateMessage = uniqid().' {message}'
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            LoggerInterface::class,
            $this->object
        );
    }

    public function testLog(): void
    {
        $this->logger
            ->expects(static::once())
            ->method('log')
            ->with(
                $level = uniqid(),
                str_replace('{message}', $message = uniqid(), $this->decorateMessage),
                $this->defaultContext,
            )
        ;

        $this->object->log($level, $message);
    }

    public function testLogWitContext(): void
    {
        $this->logger
            ->expects(static::once())
            ->method('log')
            ->with(
                $level = uniqid(),
                str_replace('{message}', $message = uniqid(), $this->decorateMessage),
                array_merge($this->defaultContext, $context = ['otherKey' => uniqid()]),
            )
        ;

        $this->object->log($level, $message, $context);
    }

    public function testLogNoMessageToken(): void
    {
        $this->object = new DecoratedLogger(
            $this->logger = $this->createMock(LoggerInterface::class),
            $this->defaultContext = ['key' => uniqid()],
            $this->decorateMessage = uniqid()
        );

        $this->logger
            ->expects(static::once())
            ->method('log')
            ->with(
                $level = uniqid(),
                $this->decorateMessage.' '.$message = uniqid(),
                $this->defaultContext,
            )
        ;

        $this->object->log($level, $message);
    }
}
