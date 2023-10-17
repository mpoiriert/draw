<?php

namespace Draw\Component\Messenger\Tests\DoctrineMessageBusHook\Event;

use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Event\EnvelopeCreatedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\EventDispatcher\Event;

#[CoversClass(EnvelopeCreatedEvent::class)]
class EnvelopeCreatedEventTest extends TestCase
{
    private EnvelopeCreatedEvent $object;

    private MessageHolderInterface&MockObject $messageHolder;

    private Envelope $envelope;

    protected function setUp(): void
    {
        $this->object = new EnvelopeCreatedEvent(
            $this->messageHolder = $this->createMock(MessageHolderInterface::class),
            $this->envelope = new Envelope((object) [])
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            Event::class,
            $this->object
        );
    }

    public function testGetMessageHolder(): void
    {
        static::assertSame(
            $this->messageHolder,
            $this->object->getMessageHolder()
        );
    }

    public function testEnvelopeMutator(): void
    {
        static::assertSame(
            $this->envelope,
            $this->object->getEnvelope()
        );

        $this->object->setEnvelope($value = new Envelope((object) []));

        static::assertSame(
            $value,
            $this->object->getEnvelope()
        );
    }
}
