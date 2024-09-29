<?php

namespace Draw\Component\Messenger\Tests\DoctrineMessageBusHook\EventListener;

use Draw\Component\Messenger\DoctrineMessageBusHook\Event\EnvelopeCreatedEvent;
use Draw\Component\Messenger\DoctrineMessageBusHook\EventListener\EnvelopeFactoryDelayStampListener;
use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * @internal
 */
#[CoversClass(EnvelopeFactoryDelayStampListener::class)]
class EnvelopeFactoryDelayStampListenerTest extends TestCase
{
    private EnvelopeFactoryDelayStampListener $object;

    private int $delay;

    protected function setUp(): void
    {
        $this->object = new EnvelopeFactoryDelayStampListener(
            $this->delay = random_int(\PHP_INT_MIN, \PHP_INT_MAX)
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame(
            [
                EnvelopeCreatedEvent::class => 'handleEnvelopeCreatedEvent',
            ],
            $this->object::getSubscribedEvents()
        );
    }

    public function testHandleEnvelopeCreatedEvent(): void
    {
        $this->object->handleEnvelopeCreatedEvent(
            $event = new EnvelopeCreatedEvent(
                $this->createMock(MessageHolderInterface::class),
                new Envelope((object) [])
            )
        );

        static::assertSame(
            $this->delay,
            $event->getEnvelope()->last(DelayStamp::class)->getDelay()
        );
    }
}
