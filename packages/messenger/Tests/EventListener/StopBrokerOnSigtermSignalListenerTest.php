<?php

namespace Draw\Component\Messenger\Tests\EventListener;

use Draw\Component\Messenger\Broker;
use Draw\Component\Messenger\Event\BrokerStartedEvent;
use Draw\Component\Messenger\EventListener\StopBrokerOnSigtermSignalListener;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Messenger\EventListener\StopBrokerOnSigtermSignalListener
 */
class StopBrokerOnSigtermSignalListenerTest extends TestCase
{
    private StopBrokerOnSigtermSignalListener $service;

    public function setUp(): void
    {
        $this->service = new StopBrokerOnSigtermSignalListener();
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                BrokerStartedEvent::class => ['onBrokerStarted', 100],
            ],
            $this->service::getSubscribedEvents()
        );
    }

    public function testOnBrokerStarted(): void
    {
        $event = $this->createMock(BrokerStartedEvent::class);
        $event
            ->expects($this->once())
            ->method('getBroker')
            ->willReturn($this->createMock(Broker::class));

        $this->service->onBrokerStarted($event);
    }
}
