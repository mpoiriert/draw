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
        static::assertSame(
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
            ->expects(static::once())
            ->method('getBroker')
            ->willReturn($this->createMock(Broker::class))
        ;

        $this->service->onBrokerStarted($event);
    }
}
