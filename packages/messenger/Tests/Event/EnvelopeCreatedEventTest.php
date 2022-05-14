<?php

namespace Draw\Component\Messenger\Tests\Event;

use Draw\Component\Messenger\Entity\MessageHolderInterface;
use Draw\Component\Messenger\Event\EnvelopeCreatedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @covers \Draw\Component\Messenger\Event\EnvelopeCreatedEvent
 */
class EnvelopeCreatedEventTest extends TestCase
{
    private EnvelopeCreatedEvent $object;

    /**
     * @var MessageHolderInterface|MockObject
     */
    private MessageHolderInterface $messageHolder;

    private Envelope $envelope;

    public function setUp(): void
    {
        $this->object = new EnvelopeCreatedEvent(
            $this->messageHolder = $this->createMock(MessageHolderInterface::class),
            $this->envelope = new Envelope((object) [])
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            Event::class,
            $this->object
        );
    }

    public function testGetMessageHolder(): void
    {
        $this->assertSame(
            $this->messageHolder,
            $this->object->getMessageHolder()
        );
    }

    public function testEnvelopeMutator(): void
    {
        $this->assertSame(
            $this->envelope,
            $this->object->getEnvelope()
        );

        $this->object->setEnvelope($value = new Envelope((object) []));

        $this->assertSame(
            $value,
            $this->object->getEnvelope()
        );
    }
}
