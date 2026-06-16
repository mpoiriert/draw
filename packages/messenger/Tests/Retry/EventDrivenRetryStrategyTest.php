<?php

namespace Draw\Component\Messenger\Tests\Retry;

use Draw\Component\Messenger\Retry\Event\GetWaitingTimeEvent;
use Draw\Component\Messenger\Retry\Event\IsRetryableEvent;
use Draw\Component\Messenger\Retry\EventDrivenRetryStrategy;
use Draw\Component\Tester\DoubleTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
class EventDrivenRetryStrategyTest extends TestCase
{
    use DoubleTrait;

    private EventDrivenRetryStrategy $object;

    private EventDispatcherInterface&MockObject $eventDispatcher;

    protected function setUp(): void
    {
        $this->object = new EventDrivenRetryStrategy(
            $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );
    }

    public function testIsRetryableDefault(): void
    {
        $envelope = new Envelope(new \stdClass());

        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->willReturnCallback(
                static function (IsRetryableEvent $event) use ($envelope) {
                    static::assertSame($envelope, $event->getEnvelope());

                    return $event;
                }
            )
            ->seal()
        ;

        static::assertFalse($this->object->isRetryable($envelope));
    }

    public function testIsRetryableViaEventTrue(): void
    {
        $envelope = new Envelope(new \stdClass());

        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->willReturnCallback(
                static function (IsRetryableEvent $event) use ($envelope) {
                    static::assertSame($envelope, $event->getEnvelope());

                    $event->setIsRetryable(true);

                    return $event;
                }
            )
            ->seal()
        ;

        static::assertTrue($this->object->isRetryable($envelope));
    }

    public function testIsRetryableViaEventFalse(): void
    {
        $envelope = new Envelope(new \stdClass());

        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->willReturnCallback(
                static function (IsRetryableEvent $event) use ($envelope) {
                    static::assertSame($envelope, $event->getEnvelope());

                    $event->setIsRetryable(false);

                    return $event;
                }
            )
            ->seal()
        ;

        static::assertFalse($this->object->isRetryable($envelope));
    }

    public function testIsRetryableFallback(): void
    {
        $envelope = new Envelope(new \stdClass());

        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->willReturnCallback(
                static function (IsRetryableEvent $event) use ($envelope) {
                    static::assertSame($envelope, $event->getEnvelope());

                    return $event;
                }
            )
            ->seal()
        ;

        $this->mockProperty(
            $this->object,
            'fallbackRetryStrategy',
            RetryStrategyInterface::class,
        )
            ->expects(static::once())
            ->method('isRetryable')
            ->with($envelope)
            ->willReturn(true)
            ->seal()
        ;

        static::assertTrue($this->object->isRetryable($envelope));
    }

    public function testGetWaitingTimeDefault(): void
    {
        $envelope = new Envelope(new \stdClass());

        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->willReturnCallback(
                static function (GetWaitingTimeEvent $event) use ($envelope) {
                    static::assertSame($envelope, $event->getEnvelope());

                    return $event;
                }
            )
            ->seal()
        ;

        static::assertSame(
            1000,
            $this->object->getWaitingTime($envelope)
        );
    }

    public function testGetWaitingTimeEvent(): void
    {
        $envelope = new Envelope(new \stdClass());

        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->willReturnCallback(
                static function (GetWaitingTimeEvent $event) use ($envelope) {
                    static::assertSame($envelope, $event->getEnvelope());

                    $event->setWaitingTime(2000);

                    return $event;
                }
            )
            ->seal()
        ;

        static::assertSame(
            2000,
            $this->object->getWaitingTime($envelope)
        );
    }

    public function testGetWaitingTimeFallback(): void
    {
        $envelope = new Envelope(new \stdClass());

        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->willReturnCallback(
                static function (GetWaitingTimeEvent $event) use ($envelope) {
                    static::assertSame($envelope, $event->getEnvelope());

                    return $event;
                }
            )
            ->seal()
        ;

        $this->mockProperty(
            $this->object,
            'fallbackRetryStrategy',
            RetryStrategyInterface::class,
        )
            ->expects(static::once())
            ->method('getWaitingTime')
            ->with($envelope)
            ->willReturn(5000)
            ->seal()
        ;

        static::assertSame(
            5000,
            $this->object->getWaitingTime($envelope)
        );
    }
}
