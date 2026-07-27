<?php

namespace Draw\Component\Messenger\Tests\Entity;

use Draw\Component\Messenger\Transport\Entity\DrawMessageTagInterface;
use Draw\Component\Messenger\Transport\Entity\DrawMessageTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
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
        $this->assertNotNull($this->entity->getId());

        $this->assertSame(
            $this->entity,
            $this->entity->setId($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->entity->getId()
        );
    }

    public function testGetMessageId(): void
    {
        $this->assertSame(
            $this->entity->getId(),
            $this->entity->getMessageId()
        );
    }

    public function testBodyMutator(): void
    {
        $this->assertNull($this->entity->getBody());

        $this->assertSame(
            $this->entity,
            $this->entity->setBody($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->entity->getBody()
        );
    }

    public function testHeadersMutator(): void
    {
        $this->assertNull($this->entity->getHeaders());

        $this->assertSame(
            $this->entity,
            $this->entity->setHeaders($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->entity->getHeaders()
        );
    }

    public function testQueueNameMutator(): void
    {
        $this->assertNull($this->entity->getQueueName());

        $this->assertSame(
            $this->entity,
            $this->entity->setQueueName($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->entity->getQueueName()
        );
    }

    public function testCreatedAtMutator(): void
    {
        $this->assertNull($this->entity->getCreatedAt());

        $this->assertSame(
            $this->entity,
            $this->entity->setCreatedAt($value = new \DateTimeImmutable())
        );

        $this->assertSame(
            $value,
            $this->entity->getCreatedAt()
        );
    }

    public function testAvailableAtMutator(): void
    {
        $this->assertNull($this->entity->getAvailableAt());

        $this->assertSame(
            $this->entity,
            $this->entity->setAvailableAt($value = new \DateTimeImmutable())
        );

        $this->assertSame(
            $value,
            $this->entity->getAvailableAt()
        );
    }

    public function testDeliveredAtMutator(): void
    {
        $this->assertNull($this->entity->getDeliveredAt());

        $this->assertSame(
            $this->entity,
            $this->entity->setDeliveredAt($value = new \DateTimeImmutable())
        );

        $this->assertSame(
            $value,
            $this->entity->getDeliveredAt()
        );
    }

    public function testTagMutator(): void
    {
        $this->assertCount(0, $this->entity->getTags());

        $this->assertSame(
            $this->entity,
            $this->entity->addTag($value = $this->createMock(DrawMessageTagInterface::class))
        );

        $this->assertCount(1, $this->entity->getTags());
        $this->assertSame(
            $value,
            $this->entity->getTags()[0]
        );

        $this->assertSame(
            $this->entity,
            $this->entity->removeTag($value)
        );

        $this->assertCount(0, $this->entity->getTags());
    }

    public function testExpiresAtMutator(): void
    {
        $this->assertNull($this->entity->getExpiresAt());

        $this->assertSame(
            $this->entity,
            $this->entity->setExpiresAt($value = new \DateTimeImmutable())
        );

        $this->assertSame(
            $value,
            $this->entity->getExpiresAt()
        );
    }

    public function testToString(): void
    {
        $this->assertSame(
            $this->entity->getId(),
            (string) $this->entity
        );
    }
}

class Message
{
    use DrawMessageTrait;
}
