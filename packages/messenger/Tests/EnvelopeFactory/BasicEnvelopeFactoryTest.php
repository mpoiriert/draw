<?php

namespace Draw\Component\Messenger\Tests\EnvelopeFactory;

use Draw\Component\Messenger\Entity\MessageHolderInterface;
use Draw\Component\Messenger\EnvelopeFactory\BasicEnvelopeFactory;
use Draw\Component\Messenger\Event\EnvelopeCreatedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class BasicEnvelopeFactoryTest extends TestCase
{
    private BasicEnvelopeFactory $object;

    /**
     * @var EventDispatcherInterface|MockObject
     */
    private EventDispatcherInterface $eventDispatcher;

    public function setUp(): void
    {
        $this->object = new BasicEnvelopeFactory(
            $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );
    }

    public function testCreateEnvelopes(): void
    {
        $messageHolder = $this->createMock(MessageHolderInterface::class);
        $messages[] = (object) [];
        $messages[] = (object) [];

        $newEnvelope = new Envelope($messages[0]);

        $this->eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [
                    $this->callback(
                        function (EnvelopeCreatedEvent $event) use ($messages, $messageHolder, $newEnvelope) {
                            $this->assertSame($messageHolder, $event->getMessageHolder());
                            $this->assertSame($messages[0], $event->getEnvelope()->getMessage());
                            $event->setEnvelope($newEnvelope);

                            return true;
                        }
                    ),
                ],
                [
                    $this->callback(
                        function (EnvelopeCreatedEvent $event) use ($messages, $messageHolder) {
                            $this->assertSame($messageHolder, $event->getMessageHolder());
                            $this->assertSame($messages[1], $event->getEnvelope()->getMessage());

                            return true;
                        }
                    ),
                ]
            )
            ->willReturnArgument(0);

        $envelopes = $this->object->createEnvelopes($messageHolder, $messages);

        $this->assertCount(
            2,
            $envelopes
        );

        $this->assertSame(
            $newEnvelope,
            $envelopes[0]
        );
    }
}
