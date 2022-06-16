<?php

namespace Draw\Component\Messenger\Tests\Broker\Event;

use Draw\Component\Messenger\Broker\Broker;
use Draw\Component\Messenger\Broker\Event\BrokerRunningEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @covers \Draw\Component\Messenger\Broker\Event\BrokerRunningEvent
 */
class BrokerRunningEventTest extends TestCase
{
    private BrokerRunningEvent $event;

    private Broker $broker;

    public function setUp(): void
    {
        $this->event = new BrokerRunningEvent(
            $this->broker = $this->createMock(Broker::class)
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            Event::class,
            $this->event
        );
    }

    public function testGetBroker(): void
    {
        static::assertSame(
            $this->broker,
            $this->event->getBroker()
        );
    }
}
