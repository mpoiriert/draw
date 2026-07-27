<?php

namespace Draw\Component\Messenger\Tests\SerializerEventDispatcher;

use Draw\Component\Messenger\SerializerEventDispatcher\Event\PostDecodeEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PostEncodeEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PreEncodeEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\EventDispatcherSerializerDecorator;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
class EventDispatcherSerializerDecoratorTest extends TestCase
{
    use MockTrait;

    private EventDispatcherSerializerDecorator $object;

    private MockObject&SerializerInterface $serializer;

    private EventDispatcherInterface&MockObject $eventDispatcher;

    protected function setUp(): void
    {
        $this->object = new EventDispatcherSerializerDecorator(
            $this->serializer = $this->createMock(SerializerInterface::class),
            $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            SerializerInterface::class,
            $this->object
        );
    }

    public function testDecode(): void
    {
        $data = ['body' => '', 'headers' => ''];

        $this->serializer
            ->expects($this->once())
            ->method('decode')
            ->with($data)
            ->willReturn($envelope = new Envelope((object) []))
        ;

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                new PostDecodeEvent($envelope)
            )
            ->willReturnArgument(0)
        ;

        $this->assertSame(
            $envelope,
            $this->object->decode($data)
        );
    }

    public function testEncode(): void
    {
        $envelope = new Envelope((object) []);

        $this->eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with(
                ...static::withConsecutive(
                    [new PreEncodeEvent($envelope)],
                    [new PostEncodeEvent($envelope)]
                )
            )
            ->willReturnArgument(0)
        ;

        $this->serializer
            ->expects($this->once())
            ->method('encode')
            ->with($envelope)
            ->willReturn($data = ['body' => '', 'headers' => ''])
        ;

        $this->assertSame(
            $data,
            $this->object->encode($envelope)
        );
    }
}
