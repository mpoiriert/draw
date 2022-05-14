<?php

namespace Draw\Component\Messenger\Tests\EventListener;

use Draw\Component\Messenger\Entity\MessageHolderInterface;
use Draw\Component\Messenger\Event\EnvelopeCreatedEvent;
use Draw\Component\Messenger\EventListener\AfterCurrentBusEnvelopeFactoryStampListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

/**
 * @covers \Draw\Component\Messenger\EventListener\AfterCurrentBusEnvelopeFactoryStampListener
 */
class AfterCurrentBusEnvelopeFactoryStampListenerTest extends TestCase
{
    private AfterCurrentBusEnvelopeFactoryStampListener $object;

    public function setUp(): void
    {
        $this->object = new AfterCurrentBusEnvelopeFactoryStampListener();
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

        $this->assertCount(
            1,
            $event->getEnvelope()->all(DispatchAfterCurrentBusStamp::class)
        );
    }
}
