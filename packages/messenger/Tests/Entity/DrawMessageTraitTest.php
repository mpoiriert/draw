<?php

namespace Draw\Component\Messenger\Tests\Entity;

use Draw\Component\Messenger\Transport\Entity\DrawMessageTagInterface;
use Draw\Component\Messenger\Transport\Entity\DrawMessageTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DrawMessageTrait::class)]
class DrawMessageTraitTest extends TestCase
{
    private Message $entity;

    protected function setUp(): void
    {
        $this->entity = new Message();
    }

    public function testIdMutator(): void
    {
        static::assertNotNull($this->entity->getId());

        static::assertSame(
            $this->entity,
            $this->entity->setId($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->entity->getId()
        );
    }

    public function testGetMessageId(): void
    {
        static::assertSame(
            $this->entity->getId(),
            $this->entity->getMessageId()
        );
    }

    public function testBodyMutator(): void
    {
        static::assertNull($this->entity->getBody());

        static::assertSame(
            $this->entity,
            $this->entity->setBody($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->entity->getBody()
        );
    }

    public function testHeadersMutator(): void
    {
        static::assertNull($this->entity->getHeaders());

        static::assertSame(
            $this->entity,
            $this->entity->setHeaders($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->entity->getHeaders()
        );
    }

    public function testQueueNameMutator(): void
    {
        static::assertNull($this->entity->getQueueName());

        static::assertSame(
            $this->entity,
            $this->entity->setQueueName($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->entity->getQueueName()
        );
    }

    public function testCreatedAtMutator(): void
    {
        static::assertNull($this->entity->getCreatedAt());

        static::assertSame(
            $this->entity,
            $this->entity->setCreatedAt($value = new \DateTimeImmutable())
        );

        static::assertSame(
            $value,
            $this->entity->getCreatedAt()
        );
    }

    public function testAvailableAtMutator(): void
    {
        static::assertNull($this->entity->getAvailableAt());

        static::assertSame(
            $this->entity,
            $this->entity->setAvailableAt($value = new \DateTimeImmutable())
        );

        static::assertSame(
            $value,
            $this->entity->getAvailableAt()
        );
    }

    public function testDeliveredAtMutator(): void
    {
        static::assertNull($this->entity->getDeliveredAt());

        static::assertSame(
            $this->entity,
            $this->entity->setDeliveredAt($value = new \DateTimeImmutable())
        );

        static::assertSame(
            $value,
            $this->entity->getDeliveredAt()
        );
    }

    public function testTagMutator(): void
    {
        static::assertCount(0, $this->entity->getTags());

        static::assertSame(
            $this->entity,
            $this->entity->addTag($value = $this->createMock(DrawMessageTagInterface::class))
        );

        static::assertCount(1, $this->entity->getTags());
        static::assertSame(
            $value,
            $this->entity->getTags()[0]
        );

        static::assertSame(
            $this->entity,
            $this->entity->removeTag($value)
        );

        static::assertCount(0, $this->entity->getTags());
    }

    public function testExpiresAtMutator(): void
    {
        static::assertNull($this->entity->getExpiresAt());

        static::assertSame(
            $this->entity,
            $this->entity->setExpiresAt($value = new \DateTimeImmutable())
        );

        static::assertSame(
            $value,
            $this->entity->getExpiresAt()
        );
    }

    public function testToString(): void
    {
        static::assertSame(
            $this->entity->getId(),
            (string) $this->entity
        );
    }
}

class Message
{
    use DrawMessageTrait;
}
