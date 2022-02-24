<?php

namespace Draw\Component\Messenger\Tests\EventListener;

use Draw\Component\Messenger\EventListener\AutoStampEnvelopeListener;
use Draw\Component\Messenger\Message\ManuallyTriggeredInterface;
use Draw\Component\Messenger\Message\StampingAwareInterface;
use Draw\Component\Messenger\Stamp\ManualTriggerStamp;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

/**
 * @covers \Draw\Component\Messenger\EventListener\AutoStampEnvelopeListener
 */
class AutoStampEnvelopeListenerTest extends TestCase implements StampingAwareInterface
{
    private $listener;

    private static $newEnvelope;

    public function setUp(): void
    {
        $this->listener = new AutoStampEnvelopeListener();
    }

    public function provideTestHandleManuallyTriggeredMessage(): iterable
    {
        yield 'no-stamp-message-object' => [
            new Envelope(new stdClass()),
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
        $this->listener->handleManuallyTriggeredMessage($event = new SendMessageToTransportsEvent($envelope));

        $this->assertCount(
            $expectedCount,
            $event->getEnvelope()->all(ManualTriggerStamp::class)
        );
    }

    public function stamp(Envelope $envelope): Envelope
    {
        return static::$newEnvelope = new Envelope(new stdClass());
    }

    public function testHandleStampingAwareMessage(): void
    {
        $envelope = new Envelope($this);

        $this->listener->handleStampingAwareMessage($event = new SendMessageToTransportsEvent($envelope));

        $this->assertSame(
            static::$newEnvelope,
            $event->getEnvelope()
        );
    }
}
