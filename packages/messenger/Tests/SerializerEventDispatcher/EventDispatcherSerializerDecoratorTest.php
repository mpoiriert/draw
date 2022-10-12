<?php

namespace Draw\Component\Messenger\Tests\SerializerEventDispatcher;

use Draw\Component\Messenger\SerializerEventDispatcher\Event\PostDecodeEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PostEncodeEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PreEncodeEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\EventDispatcherSerializerDecorator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventDispatcherSerializerDecoratorTest extends TestCase
{
    private EventDispatcherSerializerDecorator $object;

    /**
     * @var SerializerInterface&MockObject
     */
    private $serializer;

    /**
     * @var EventDispatcherInterface&MockObject
     */
    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        $this->object = new EventDispatcherSerializerDecorator(
            $this->serializer = $this->createMock(SerializerInterface::class),
            $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            SerializerInterface::class,
            $this->object
        );
    }

    public function testDecode(): void
    {
        $data = ['body' => '', 'headers' => ''];

        $this->serializer
            ->expects(static::once())
            ->method('decode')
            ->with($data)
            ->willReturn($envelope = new Envelope((object) []));

        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                new PostDecodeEvent($envelope)
            )
            ->willReturnArgument(0);

        static::assertSame(
            $envelope,
            $this->object->decode($data)
        );
    }

    public function testEncode(): void
    {
        $envelope = new Envelope((object) []);

        $this->eventDispatcher
            ->expects(static::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [new PreEncodeEvent($envelope)],
                [new PostEncodeEvent($envelope)]
            )
            ->willReturnArgument(0);

        $this->serializer
            ->expects(static::once())
            ->method('encode')
            ->with($envelope)
            ->willReturn($data = ['body' => '', 'headers' => '']);

        static::assertSame(
            $data,
            $this->object->encode($envelope)
        );
    }
}
