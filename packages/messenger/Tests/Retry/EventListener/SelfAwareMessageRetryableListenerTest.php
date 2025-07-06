<?php

namespace Draw\Component\Messenger\Tests\Retry\EventListener;

use Draw\Component\Messenger\Retry\Event\GetWaitingTimeEvent;
use Draw\Component\Messenger\Retry\Event\IsRetryableEvent;
use Draw\Component\Messenger\Retry\EventListener\SelfAwareMessageRetryableListener;
use Draw\Component\Messenger\Retry\Message\SelfAwareRetryableMessageInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @internal
 */
class SelfAwareMessageRetryableListenerTest extends TestCase
{
    private SelfAwareMessageRetryableListener $object;

    protected function setUp(): void
    {
        $this->object = new SelfAwareMessageRetryableListener();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            ResetInterface::class,
            $this->object
        );
    }

    public function testOnIsRetryableEventNoSelfAwareMessage(): void
    {
        $this->object->onIsRetryableEvent(
            $event = new IsRetryableEvent(
                new Envelope(new \stdClass()),
            )
        );

        static::assertNull(
            $event->getIsRetryable()
        );
    }

    public function testOnIsRetryableEventNotRetryable(): void
    {
        $message = $this->createMock(SelfAwareRetryableMessageInterface::class);
        $message->expects(static::once())
            ->method('getRetryWaitingTime')
            ->willReturn(null)
        ;

        $envelope = new Envelope($message);

        $this->object->onIsRetryableEvent(
            $event = new IsRetryableEvent($envelope, new \Exception())
        );

        static::assertFalse(
            $event->getIsRetryable()
        );
    }

    public function testOnIsRetryableEventRetryable(): void
    {
        $message = $this->createMock(SelfAwareRetryableMessageInterface::class);
        $message->expects(static::once())
            ->method('getRetryWaitingTime')
            ->with(
                static::isInstanceOf(Envelope::class),
                static::isInstanceOf(\Exception::class),
                0 // Assuming this is the first retry
            )
            ->willReturn(1000)
        ;

        $envelope = new Envelope($message);

        $this->object->onIsRetryableEvent(
            $event = new IsRetryableEvent($envelope, new \Exception())
        );

        static::assertTrue(
            $event->getIsRetryable()
        );
    }

    public function testOnGetWaitingTimeEventNoWaitingTime(): void
    {
        $envelope = new Envelope(new \stdClass());

        $event = new GetWaitingTimeEvent($envelope);

        $this->object->onGetWaitingTimeEvent($event);

        static::assertNull($event->getWaitingTime());
    }

    public function testOnGetWaitingTimeEventWithWaitingTime(): void
    {
        $message = $this->createMock(SelfAwareRetryableMessageInterface::class);
        $message->expects(static::once())
            ->method('getRetryWaitingTime')
            ->willReturn(1000)
        ;

        $envelope = new Envelope($message);

        $this->object->onIsRetryableEvent(
            new IsRetryableEvent($envelope, new \Exception())
        );

        $event = new GetWaitingTimeEvent($envelope);

        $this->object->onGetWaitingTimeEvent($event);

        static::assertSame(1000, $event->getWaitingTime());
    }

    public function testReset(): void
    {
        $message = $this->createMock(SelfAwareRetryableMessageInterface::class);
        $message->expects(static::once())
            ->method('getRetryWaitingTime')
            ->willReturn(1000)
        ;

        $envelope = new Envelope($message);

        $this->object->onIsRetryableEvent(
            new IsRetryableEvent($envelope, new \Exception())
        );

        $this->object->reset();

        $this->object->onGetWaitingTimeEvent(
            $event = new GetWaitingTimeEvent($envelope)
        );

        static::assertNull($event->getWaitingTime());
    }
}
