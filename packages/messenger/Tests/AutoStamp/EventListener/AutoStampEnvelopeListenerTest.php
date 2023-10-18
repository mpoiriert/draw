<?php

namespace Draw\Component\Messenger\Tests\AutoStamp\EventListener;

use Draw\Component\Messenger\AutoStamp\EventListener\AutoStampEnvelopeListener;
use Draw\Component\Messenger\AutoStamp\Message\StampingAwareInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

#[CoversClass(AutoStampEnvelopeListener::class)]
class AutoStampEnvelopeListenerTest extends TestCase implements StampingAwareInterface
{
    private AutoStampEnvelopeListener $object;

    private static Envelope $newEnvelope;

    protected function setUp(): void
    {
        $this->object = new AutoStampEnvelopeListener();
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
                SendMessageToTransportsEvent::class => [
                    ['handleStampingAwareMessage'],
                ],
            ],
            $this->object::getSubscribedEvents()
        );
    }

    public function stamp(Envelope $envelope): Envelope
    {
        return self::$newEnvelope = new Envelope((object) []);
    }

    public function testHandleStampingAwareMessage(): void
    {
        $envelope = new Envelope($this);

        $this->object->handleStampingAwareMessage($event = new SendMessageToTransportsEvent($envelope, []));

        static::assertSame(
            self::$newEnvelope,
            $event->getEnvelope()
        );
    }

    public function testHandleStampingAwareMessageMessageStampingAwareInterface(): void
    {
        $envelope = new Envelope(
            $message = new class() {
                public bool $called = false;

                public function stamp(): void
                {
                    $this->called = true;
                }
            }
        );

        $this->object->handleStampingAwareMessage(new SendMessageToTransportsEvent($envelope, []));

        static::assertFalse($message->called, 'Stamp should not have been called.');
    }
}
