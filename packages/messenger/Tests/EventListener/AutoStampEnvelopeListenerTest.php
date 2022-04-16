<?php

namespace Draw\Component\Messenger\Tests\EventListener;

use Draw\Component\Messenger\EventListener\AutoStampEnvelopeListener;
use Draw\Component\Messenger\Message\ManuallyTriggeredInterface;
use Draw\Component\Messenger\Message\StampingAwareInterface;
use Draw\Component\Messenger\Stamp\ManualTriggerStamp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

/**
 * @covers \Draw\Component\Messenger\EventListener\AutoStampEnvelopeListener
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
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->service
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                SendMessageToTransportsEvent::class => [
                    ['handleManuallyTriggeredMessage'],
                    ['handleStampingAwareMessage'],
                ],
            ],
            $this->service::getSubscribedEvents()
        );
    }

    public function provideTestHandleManuallyTriggeredMessage(): iterable
    {
        yield 'no-stamp-message-object' => [
            new Envelope((object) []),
            0,
        ];

        yield 'stamp-manually-triggered' => [
            new Envelope($this->createMock(ManuallyTriggeredInterface::class)),
            1,
        ];

        yield 'already-stamp-manually-triggered' => [
            new Envelope($this->createMock(ManuallyTriggeredInterface::class), [new ManualTriggerStamp()]),
            1,
        ];
    }

    /**
     * @dataProvider provideTestHandleManuallyTriggeredMessage
     */
    public function testHandleManuallyTriggeredMessage(Envelope $envelope, int $expectedCount): void
    {
        $this->service->handleManuallyTriggeredMessage($event = new SendMessageToTransportsEvent($envelope));

        $this->assertCount(
            $expectedCount,
            $event->getEnvelope()->all(ManualTriggerStamp::class)
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

        $this->assertSame(
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

        $this->assertFalse($message->called, 'Stamp should not have been called.');
    }
}
