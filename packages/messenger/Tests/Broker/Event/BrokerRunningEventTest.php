<?php

namespace Draw\Component\Messenger\Tests\Broker\Event;

use Draw\Component\Messenger\Broker\Broker;
use Draw\Component\Messenger\Broker\Event\BrokerRunningEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(BrokerRunningEvent::class)]
class BrokerRunningEventTest extends TestCase
{
    private BrokerRunningEvent $event;

    private Broker&Stub $broker;

    protected function setUp(): void
    {
        $this->event = new BrokerRunningEvent(
            $this->broker = static::createStub(Broker::class)
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
