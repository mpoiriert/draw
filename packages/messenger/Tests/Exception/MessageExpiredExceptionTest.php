<?php

namespace Draw\Component\Messenger\Tests\Exception;

use DateTimeImmutable;
use DateTimeInterface;
use Draw\Component\Messenger\Exception\MessageExpiredException;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Messenger\Exception\MessageExpiredException
 */
class MessageExpiredExceptionTest extends TestCase
{
    private MessageExpiredException $exception;

    private string $messageId;

    private DateTimeInterface $expiredAt;

    public function setUp(): void
    {
        $this->exception = new MessageExpiredException(
            $this->messageId = uniqid('message-id-'),
            $this->expiredAt = new DateTimeImmutable()
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            Exception::class,
            $this->exception
        );
    }

    public function testGetMessage(): void
    {
        $this->assertSame(
            sprintf(
                'Message id [%s] expired on [%s]', $this->messageId, $this->expiredAt->format('c')
            ),
            $this->exception->getMessage()
        );
    }

    public function testGetGetExpiredAt(): void
    {
        $this->assertSame(
            $this->expiredAt,
            $this->exception->getExpiredAt()
        );
    }
}
