<?php

namespace Draw\Component\Messenger\Tests\Broker\Event;

use Draw\Component\Messenger\Broker\Broker;
use Draw\Component\Messenger\Broker\Event\BrokerStartedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(BrokerStartedEvent::class)]
class BrokerStartedEventTest extends TestCase
{
    private BrokerStartedEvent $event;

    private Broker $broker;

    private int $concurrent;

    private int $timeout;

    protected function setUp(): void
    {
        $this->event = new BrokerStartedEvent(
            $this->broker = $this->createMock(Broker::class),
            $this->concurrent = random_int(1, \PHP_INT_MAX),
            $this->timeout = random_int(1, \PHP_INT_MAX)
        );
    }

    public function testGetBroker(): void
    {
        static::assertSame(
            $this->broker,
            $this->event->getBroker()
        );
    }

    public function testGetConcurrent(): void
    {
        static::assertSame(
            $this->concurrent,
            $this->event->getConcurrent()
        );
    }

    public function testGetTimeout(): void
    {
        static::assertSame(
            $this->timeout,
            $this->event->getTimeout()
        );
    }
}
