<?php

namespace Draw\Component\OpenApi\Tests\Event;

use Draw\Component\OpenApi\Event\PreSerializerResponseEvent;
use Draw\Component\OpenApi\Serializer\Serialization;
use JMS\Serializer\SerializationContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PreSerializerResponseEvent::class)]
class PreSerializerResponseEventTest extends TestCase
{
    private PreSerializerResponseEvent $object;

    private object $result;

    private Serialization $serialization;

    private SerializationContext $serializationContext;

    protected function setUp(): void
    {
        $this->object = new PreSerializerResponseEvent(
            $this->result = (object) [],
            $this->serialization = new Serialization(),
            $this->serializationContext = new SerializationContext()
        );
    }

    public function testGetResult(): void
    {
        static::assertSame(
            $this->result,
            $this->object->getResult()
        );
    }

    public function testGetSerialization(): void
    {
        static::assertSame(
            $this->serialization,
            $this->object->getSerialization()
        );
    }

    public function testGetContext(): void
    {
        static::assertSame(
            $this->serializationContext,
            $this->object->getContext()
        );
    }
}
