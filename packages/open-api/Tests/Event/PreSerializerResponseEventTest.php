<?php

namespace Draw\Component\OpenApi\Tests\Event;

use Draw\Component\OpenApi\Configuration\Serialization;
use Draw\Component\OpenApi\Event\PreSerializerResponseEvent;
use JMS\Serializer\SerializationContext;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\OpenApi\Event\PreSerializerResponseEvent
 */
class PreSerializerResponseEventTest extends TestCase
{
    private PreSerializerResponseEvent $object;

    private object $result;

    private Serialization $serialization;

    private SerializationContext $serializationContext;

    public function setUp(): void
    {
        $this->object = new PreSerializerResponseEvent(
            $this->result = (object) [],
            $this->serialization = new Serialization([]),
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
