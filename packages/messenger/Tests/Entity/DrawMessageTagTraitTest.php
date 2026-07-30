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
        $this->assertNull($this->entity->getName());

        $this->assertSame(
            $this->entity,
            $this->entity->setName($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->entity->getName()
        );
    }

    public function testMessageMutator(): void
    {
        $this->assertNull($this->entity->getMessage());

        $this->assertSame(
            $this->entity,
            $this->entity->setMessage($value = $this->createStub(DrawMessageInterface::class))
        );

        $this->assertSame(
            $value,
            $this->entity->getMessage()
        );
    }

    public function testToString(): void
    {
        $this->assertSame('', (string) $this->entity);

        $this->entity->setName($value = uniqid());

        $this->assertSame(
            $value,
            (string) $this->entity
        );
    }
}

class Tag
{
    use DrawMessageTagTrait;
}
