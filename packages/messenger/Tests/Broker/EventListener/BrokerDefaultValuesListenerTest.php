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

    private array $contexts;

    public function setUp(): void
    {
        $this->service = new BrokerDefaultValuesListener(
            $this->contexts = [
                uniqid('context-1') => [
                    'receivers' => [uniqid('receiver-1-'), uniqid('receiver-2-')],
                    'defaultOptions' => [
                        uniqid('option-1-') => uniqid('value-1-'),
                        uniqid('option-2-') => uniqid('value-2-'),
                    ],
                ],
                uniqid('context-2') => [
                    'receivers' => [uniqid('receiver-3-'), uniqid('receiver-4-')],
                    'defaultOptions' => [
                        uniqid('option-3-') => uniqid('value-3-'),
                    ],
                ],
            ]
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            EventSubscriberInterface::class,
            $this->service
        );
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame(
            [
                NewConsumerProcessEvent::class => ['initializeDefaultValues', 255],
            ],
            $this->service::getSubscribedEvents()
        );
    }

    public function testInitializeDefaultValues(): void
    {
        $contextName = array_key_first($this->contexts);
        $options = [
            array_keys($this->contexts[$contextName]['defaultOptions'])[0] => uniqid('kept-value-1'),
        ];
        $event = new NewConsumerProcessEvent(
            $contextName,
            [],
            $options
        );

        $this->service->initializeDefaultValues($event);

        static::assertSame(
            $this->contexts[$contextName]['receivers'],
            $event->getReceivers()
        );

        static::assertSame(
            $event->getOptions(),
            array_merge(
                $this->contexts[$contextName]['defaultOptions'],
                $options
            )
        );
    }
}
