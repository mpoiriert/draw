<?php

namespace Draw\Component\Messenger\Tests\Broker\EventListener;

use Draw\Component\Messenger\Broker\Event\NewConsumerProcessEvent;
use Draw\Component\Messenger\Broker\EventListener\BrokerDefaultValuesListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @covers \Draw\Component\Messenger\Broker\EventListener\BrokerDefaultValuesListener
 */
class BrokerDefaultValuesListenerTest extends TestCase
{
    private BrokerDefaultValuesListener $service;

    private array $receivers;

    private array $defaultOptions;

    public function setUp(): void
    {
        $this->service = new BrokerDefaultValuesListener(
            $this->receivers = [uniqid('receiver-1-'), uniqid('receiver-2-')],
            $this->defaultOptions = [
                uniqid('option-1-') => uniqid('value-1-'),
                uniqid('option-2-') => uniqid('value-2-'),
            ]
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->service
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                NewConsumerProcessEvent::class => ['initializeDefaultValues', 255],
            ],
            $this->service::getSubscribedEvents()
        );
    }

    public function testInitializeDefaultValues(): void
    {
        $event = new NewConsumerProcessEvent(
            [],
            $options = [array_keys($this->defaultOptions)[0] => uniqid('kept-value-1')]
        );

        $this->service->initializeDefaultValues($event);

        $this->assertSame(
            $this->receivers,
            $event->getReceivers()
        );

        $this->assertSame(
            $event->getOptions(),
            array_merge(
                $this->defaultOptions,
                $options
            )
        );
    }
}
