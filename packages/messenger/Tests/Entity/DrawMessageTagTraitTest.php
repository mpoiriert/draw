<?php

namespace Draw\Component\Messenger\Tests\Entity;

use Draw\Component\Messenger\Transport\Entity\DrawMessageInterface;
use Draw\Component\Messenger\Transport\Entity\DrawMessageTagTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DrawMessageTagTrait::class)]
class DrawMessageTagTraitTest extends TestCase
{
    private Tag $entity;

    protected function setUp(): void
    {
        $this->entity = new Tag();
    }

    public function testNameMutator(): void
    {
        static::assertNull($this->entity->getName());

        static::assertSame(
            $this->entity,
            $this->entity->setName($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->entity->getName()
        );
    }

    public function testMessageMutator(): void
    {
        static::assertNull($this->entity->getMessage());

        static::assertSame(
            $this->entity,
            $this->entity->setMessage($value = $this->createMock(DrawMessageInterface::class))
        );

        static::assertSame(
            $value,
            $this->entity->getMessage()
        );
    }

    public function testToString(): void
    {
        static::assertSame('', (string) $this->entity);

        $this->entity->setName($value = uniqid());

        static::assertSame(
            $value,
            (string) $this->entity
        );
    }
}

class Tag
{
    use DrawMessageTagTrait;
}
