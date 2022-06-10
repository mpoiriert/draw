<?php

namespace Draw\Component\Messenger\Tests\Broker\Event;

use Draw\Component\Messenger\Broker\Event\NewConsumerProcessEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @covers \Draw\Component\Messenger\Broker\Event\NewConsumerProcessEvent
 */
class NewConsumerProcessEventTest extends TestCase
{
    private NewConsumerProcessEvent $event;

    private array $receivers;

    private array $options;

    public function setUp(): void
    {
        $this->event = new NewConsumerProcessEvent(
            $this->receivers = [uniqid('receiver-1-'), uniqid('receiver-2-')],
            $this->options = [uniqid('option-1-'), uniqid('option-2-')]
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            Event::class,
            $this->event
        );
    }

    public function testReceiversMutator(): void
    {
        $this->assertSame(
            $this->receivers,
            $this->event->getReceivers()
        );

        $this->assertSame(
            $this->event,
            $this->event->setReceivers($value = [uniqid('receiver-1-'), uniqid('receiver-2-')])
        );

        $this->assertSame(
            $value,
            $this->event->getReceivers()
        );
    }

    public function testOptionsMutator(): void
    {
        $this->assertSame(
            $this->options,
            $this->event->getOptions()
        );

        $this->assertSame(
            $this->event,
            $this->event->setOptions($value = [uniqid('option-1-'), uniqid('option-2-')])
        );

        $this->assertSame(
            $value,
            $this->event->getOptions()
        );
    }
}
