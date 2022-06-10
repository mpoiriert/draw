<?php

namespace Draw\Component\Messenger\Tests\Searchable;

use Draw\Component\Messenger\Searchable\EnvelopeFinder;
use Draw\Component\Messenger\Searchable\Stamp\FoundFromTransportStamp;
use Draw\Component\Messenger\Searchable\TransportRepository;
use Draw\Component\Tester\MockBuilderTrait;
use Draw\Contracts\Messenger\Exception\MessageNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @covers \Draw\Component\Messenger\Searchable\EnvelopeFinder
 */
class EnvelopeFinderTest extends TestCase
{
    use MockBuilderTrait;

    private EnvelopeFinder $service;

    /**
     * @var TransportRepository|MockObject
     */
    private TransportRepository $transportRepository;

    public function setUp(): void
    {
        $this->service = new EnvelopeFinder(
            $this->transportRepository = $this->createMock(TransportRepository::class),
        );
    }

    public function testFindByIdNotFound(): void
    {
        $this->transportRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn(
                $transports = [
                    $transport = $this->createMock(ListableReceiverInterface::class),
                    $transport,
                ]
            );

        $transport
            ->expects($this->exactly(count($transports)))
            ->method('find')
            ->with($messageId = uniqid('message-id'))
            ->willReturn(null);

        static::expectException(MessageNotFoundException::class);

        $this->service->findById($messageId);
    }

    public function testFindByIdNotListableReceiver(): void
    {
        $this->transportRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn(
                [
                    $transport = $this->createMockWithExtraMethods(TransportInterface::class, ['find']),
                    $transport,
                ]
            );

        $transport
            ->expects($this->never())
            ->method('find');

        static::expectException(MessageNotFoundException::class);

        $this->service->findById(uniqid('message-id-'));
    }

    public function testFindById(): void
    {
        $this->transportRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn(
                [
                    ($transportName = uniqid('transport-')) => $transport = $this->createMock(ListableReceiverInterface::class),
                ]
            );

        $transport
            ->expects($this->once())
            ->method('find')
            ->with($messageId = uniqid('message-id-'))
            ->willReturn(new Envelope((object) []));

        $this->assertNotNull($envelope = $this->service->findById($messageId));

        $this->assertNotNull(
            $stamp = $envelope->last(FoundFromTransportStamp::class)
        );

        $this->assertSame(
            $transportName,
            $stamp->getTransportName()
        );
    }
}
