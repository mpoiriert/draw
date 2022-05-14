<?php

namespace Draw\Component\Messenger\Tests\EventListener;

use Draw\Component\Messenger\Entity\MessageHolderInterface;
use Draw\Component\Messenger\Event\EnvelopeCreatedEvent;
use Draw\Component\Messenger\EventListener\EnvelopeFactoryDelayStampListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * @covers \Draw\Component\Messenger\EventListener\EnvelopeFactoryDelayStampListener
 */
class EnvelopeFactoryDelayStampListenerTest extends TestCase
{
    private EnvelopeFactoryDelayStampListener $object;

    private int $delay;

    public function setUp(): void
    {
        $this->object = new EnvelopeFactoryDelayStampListener(
            $this->delay = rand(PHP_INT_MIN, PHP_INT_MAX)
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
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

        $this->assertSame(
            $this->delay,
            $event->getEnvelope()->last(DelayStamp::class)->getDelay()
        );
    }
}
