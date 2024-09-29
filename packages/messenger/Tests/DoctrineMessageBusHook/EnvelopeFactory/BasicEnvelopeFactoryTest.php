<?php

namespace Draw\Component\Messenger\Tests\DoctrineMessageBusHook\EnvelopeFactory;

use Draw\Component\Messenger\DoctrineMessageBusHook\EnvelopeFactory\BasicEnvelopeFactory;
use Draw\Component\Messenger\DoctrineMessageBusHook\Event\EnvelopeCreatedEvent;
use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderInterface;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
class BasicEnvelopeFactoryTest extends TestCase
{
    use MockTrait;
    private BasicEnvelopeFactory $object;

    /**
     * @var EventDispatcherInterface&MockObject
     */
    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        $this->object = new BasicEnvelopeFactory(
            $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );
    }

    public function testCreateEnvelopes(): void
    {
        $messageHolder = $this->createMock(MessageHolderInterface::class);
        $messages = [
            (object) [],
            (object) [],
        ];

        $newEnvelope = new Envelope($messages[0]);

        $this->eventDispatcher
            ->expects(static::exactly(2))
            ->method('dispatch')
            ->with(
                ...static::withConsecutive(
                    [
                        static::callback(
                            function (EnvelopeCreatedEvent $event) use ($messages, $messageHolder, $newEnvelope) {
                                $this->assertSame($messageHolder, $event->getMessageHolder());
                                $this->assertSame($messages[0], $event->getEnvelope()->getMessage());
                                $event->setEnvelope($newEnvelope);

                                return true;
                            }
                        ),
                    ],
                    [
                        static::callback(
                            function (EnvelopeCreatedEvent $event) use ($messages, $messageHolder) {
                                $this->assertSame($messageHolder, $event->getMessageHolder());
                                $this->assertSame($messages[1], $event->getEnvelope()->getMessage());

                                return true;
                            }
                        ),
                    ]
                )
            )
            ->willReturnArgument(0)
        ;

        $envelopes = $this->object->createEnvelopes($messageHolder, $messages);

        static::assertCount(
            2,
            $envelopes
        );

        static::assertSame(
            $newEnvelope,
            $envelopes[0]
        );
    }
}
