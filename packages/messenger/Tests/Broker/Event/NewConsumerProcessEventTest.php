<?php

namespace Draw\Component\Messenger\Tests\Broker\Event;

use Draw\Component\Messenger\Broker\Event\NewConsumerProcessEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @internal
 */
#[CoversClass(NewConsumerProcessEvent::class)]
class NewConsumerProcessEventTest extends TestCase
{
    private NewConsumerProcessEvent $event;

    private string $context;

    private array $receivers;

    private array $options;

    protected function setUp(): void
    {
        $this->event = new NewConsumerProcessEvent(
            $this->context = uniqid('context-'),
            $this->receivers = [uniqid('receiver-1-'), uniqid('receiver-2-')],
            $this->options = [uniqid('option-1-'), uniqid('option-2-')]
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            Event::class,
            $this->event
        );
    }

    public function testGetContext(): void
    {
        static::assertSame(
            $this->context,
            $this->event->getContext()
        );
    }

    public function testReceiversMutator(): void
    {
        static::assertSame(
            $this->receivers,
            $this->event->getReceivers()
        );

        static::assertSame(
            $this->event,
            $this->event->setReceivers($value = [uniqid('receiver-1-'), uniqid('receiver-2-')])
        );

        static::assertSame(
            $value,
            $this->event->getReceivers()
        );
    }

    public function testOptionsMutator(): void
    {
        static::assertSame(
            $this->options,
            $this->event->getOptions()
        );

        static::assertSame(
            $this->event,
            $this->event->setOptions($value = [uniqid('option-1-'), uniqid('option-2-')])
        );

        static::assertSame(
            $value,
            $this->event->getOptions()
        );
    }
}
