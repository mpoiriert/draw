<?php

namespace Draw\Component\Log\Tests;

use Draw\Component\Log\DecoratedLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
class DecoratedLoggerTest extends TestCase
{
    public function testLog(): void
    {
        $object = new DecoratedLogger(
            $logger = $this->createMock(LoggerInterface::class),
            $defaultContext = ['key' => uniqid()],
            $decorateMessage = uniqid().' {message}'
        );

        $logger
            ->expects(static::once())
            ->method('log')
            ->with(
                $level = uniqid(),
                str_replace('{message}', $message = uniqid(), $decorateMessage),
                $defaultContext,
            )
        ;

        $object->log($level, $message);
    }

    public function testLogWitContext(): void
    {
        $object = new DecoratedLogger(
            $logger = $this->createMock(LoggerInterface::class),
            $defaultContext = ['key' => uniqid()],
            $decorateMessage = uniqid().' {message}'
        );

        $logger
            ->expects(static::once())
            ->method('log')
            ->with(
                $level = uniqid(),
                str_replace('{message}', $message = uniqid(), $decorateMessage),
                array_merge($defaultContext, $context = ['otherKey' => uniqid()]),
            )
        ;

        $object->log($level, $message, $context);
    }

    public function testLogNoMessageToken(): void
    {
        $object = new DecoratedLogger(
            $logger = $this->createMock(LoggerInterface::class),
            $defaultContext = ['key' => uniqid()],
            $decorateMessage = uniqid()
        );

        $logger
            ->expects(static::once())
            ->method('log')
            ->with(
                $level = uniqid(),
                $decorateMessage.' '.$message = uniqid(),
                $defaultContext,
            )
        ;

        $object->log($level, $message);
    }
}
