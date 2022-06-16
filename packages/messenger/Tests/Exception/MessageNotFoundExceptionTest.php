<?php

namespace Draw\Component\Messenger\Tests\Exception;

use Draw\Contracts\Messenger\Exception\MessageNotFoundException;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Contracts\Messenger\Exception\MessageNotFoundException
 */
class MessageNotFoundExceptionTest extends TestCase
{
    private MessageNotFoundException $exception;

    private string $messageId;

    protected function setUp(): void
    {
        $this->exception = new MessageNotFoundException(
            $this->messageId = uniqid('message-id-'),
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            Exception::class,
            $this->exception
        );
    }

    public function testGetMessage(): void
    {
        static::assertSame(
            sprintf('Message id [%s] not found', $this->messageId),
            $this->exception->getMessage()
        );
    }
}
