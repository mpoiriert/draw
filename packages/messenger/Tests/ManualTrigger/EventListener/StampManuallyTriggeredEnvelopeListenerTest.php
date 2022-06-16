<?php

namespace Draw\Component\Messenger\Tests\ManualTrigger\EventListener;

use Draw\Component\Messenger\ManualTrigger\EventListener\StampManuallyTriggeredEnvelopeListener;
use Draw\Component\Messenger\ManualTrigger\Message\ManuallyTriggeredInterface;
use Draw\Component\Messenger\ManualTrigger\Stamp\ManualTriggerStamp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

/**
 * @covers \Draw\Component\Messenger\ManualTrigger\EventListener\StampManuallyTriggeredEnvelopeListener
 */
class StampManuallyTriggeredEnvelopeListenerTest extends TestCase
{
    private StampManuallyTriggeredEnvelopeListener $service;

    private static Envelope $newEnvelope;

    protected function setUp(): void
    {
        $this->service = new StampManuallyTriggeredEnvelopeListener();
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
                    ['handleManuallyTriggeredMessage'],
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

        static::assertCount(
            $expectedCount,
            $event->getEnvelope()->all(ManualTriggerStamp::class)
        );
    }
}
