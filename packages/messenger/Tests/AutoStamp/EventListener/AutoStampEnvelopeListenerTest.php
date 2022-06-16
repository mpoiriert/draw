<?php

namespace Draw\Component\Messenger\Tests\AutoStamp\EventListener;

use Draw\Component\Messenger\AutoStamp\EventListener\AutoStampEnvelopeListener;
use Draw\Component\Messenger\AutoStamp\Message\StampingAwareInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

/**
 * @covers \Draw\Component\Messenger\AutoStamp\EventListener\AutoStampEnvelopeListener
 */
class AutoStampEnvelopeListenerTest extends TestCase implements StampingAwareInterface
{
    private AutoStampEnvelopeListener $service;

    private static Envelope $newEnvelope;

    public function setUp(): void
    {
        $this->service = new AutoStampEnvelopeListener();
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
                SendMessageToTransportsEvent::class => [
                    ['handleStampingAwareMessage'],
                ],
            ],
            $this->service::getSubscribedEvents()
        );
    }

    public function stamp(Envelope $envelope): Envelope
    {
        return static::$newEnvelope = new Envelope((object) []);
    }

    public function testHandleStampingAwareMessage(): void
    {
        $envelope = new Envelope($this);

        $this->service->handleStampingAwareMessage($event = new SendMessageToTransportsEvent($envelope));

        static::assertSame(
            static::$newEnvelope,
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

        $this->service->handleStampingAwareMessage(new SendMessageToTransportsEvent($envelope));

        static::assertFalse($message->called, 'Stamp should not have been called.');
    }
}
