<?php

namespace Draw\Component\Messenger\Tests\Broker\EventListener;

use Draw\Component\Messenger\Broker\Broker;
use Draw\Component\Messenger\Broker\Event\BrokerStartedEvent;
use Draw\Component\Messenger\Broker\EventListener\StopBrokerOnSigtermSignalListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(StopBrokerOnSigtermSignalListener::class)]
class StopBrokerOnSigtermSignalListenerTest extends TestCase
{
    private StopBrokerOnSigtermSignalListener $service;

    protected function setUp(): void
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
            ->willReturn($this->createStub(Broker::class))
        ;

        $this->service->onBrokerStarted($event);
    }
}
