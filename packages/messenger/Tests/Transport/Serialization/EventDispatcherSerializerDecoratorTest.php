<?php

namespace Draw\Component\Messenger\Tests\Transport\Serialization;

use Draw\Component\Messenger\Transport\Event\PostDecodeEvent;
use Draw\Component\Messenger\Transport\Event\PostEncodeEvent;
use Draw\Component\Messenger\Transport\Event\PreEncodeEvent;
use Draw\Component\Messenger\Transport\Serialization\EventDispatcherSerializerDecorator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventDispatcherSerializerDecoratorTest extends TestCase
{
    private EventDispatcherSerializerDecorator $object;

    private SerializerInterface $serializer;

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
            );

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
            );

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
