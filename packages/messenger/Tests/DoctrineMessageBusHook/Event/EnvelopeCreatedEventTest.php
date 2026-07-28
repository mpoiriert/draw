<?php

namespace Draw\Component\Messenger\Tests\DoctrineMessageBusHook\Event;

use Draw\Component\Messenger\DoctrineMessageBusHook\Event\EnvelopeCreatedEvent;
use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;

/**
 * @internal
 */
#[CoversClass(EnvelopeCreatedEvent::class)]
class EnvelopeCreatedEventTest extends TestCase
{
    private EnvelopeCreatedEvent $object;

    private MessageHolderInterface&Stub $messageHolder;

    private Envelope $envelope;

    protected function setUp(): void
    {
        $this->object = new EnvelopeCreatedEvent(
            $this->messageHolder = $this->createStub(MessageHolderInterface::class),
            $this->envelope = new Envelope((object) [])
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
