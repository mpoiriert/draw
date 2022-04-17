<?php

namespace Draw\Component\Messenger\Tests;

use Draw\Component\Messenger\EnvelopeFinder;
use Draw\Component\Messenger\Stamp\FindFromTransportStamp;
use Draw\Component\Tester\MockBuilderTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @covers \Draw\Component\Messenger\EnvelopeFinder
 */
class EnvelopeFinderTest extends \PHPUnit\Framework\TestCase
{
    use MockBuilderTrait;

    private EnvelopeFinder $service;

    private ContainerInterface $transportLocator;

    private array $transportNames;

    public function setUp(): void
    {
        $this->service = new EnvelopeFinder(
            $this->transportLocator = $this->createMock(ContainerInterface::class),
            $this->transportNames = [uniqid('transport-1-'), uniqid('transport-2-')]
        );
    }

    public function testFindByIdNotFound(): void
    {
        $this->transportLocator
            ->expects($this->exactly(count($this->transportNames)))
            ->method('get')
            ->withConsecutive(
                [$this->transportNames[0]],
                [$this->transportNames[1]]
            )
            ->willReturn($transport = $this->createMock(ListableReceiverInterface::class));

        $transport
            ->expects($this->exactly(count($this->transportNames)))
            ->method('find')
            ->with($messageId = uniqid('message-id'))
            ->willReturn(null);

        $this->assertNull($this->service->findById($messageId));
    }

    public function testFindByIdNotListableReceiver(): void
    {
        $this->transportLocator
            ->expects($this->exactly(count($this->transportNames)))
            ->method('get')
            ->withConsecutive(
                [$this->transportNames[0]],
                [$this->transportNames[1]]
            )
            ->willReturn($transport = $this->createMockWithExtraMethods(TransportInterface::class, ['find']));

        $transport
            ->expects($this->never())
            ->method('find');

        $this->assertNull($this->service->findById(uniqid('message-id-')));
    }

    public function testFindById(): void
    {
        $this->transportLocator
            ->expects($this->once())
            ->method('get')
            ->with($this->transportNames[0])
            ->willReturn($transport = $this->createMock(ListableReceiverInterface::class));

        $transport
            ->expects($this->once())
            ->method('find')
            ->with($messageId = uniqid('message-id-'))
            ->willReturn(new Envelope((object) []));

        $this->assertNotNull($envelope = $this->service->findById($messageId));

        $this->assertNotNull(
            $stamp = $envelope->last(FindFromTransportStamp::class)
        );

        $this->assertSame(
            $this->transportNames[0],
            $stamp->getTransportName()
        );
    }
}
