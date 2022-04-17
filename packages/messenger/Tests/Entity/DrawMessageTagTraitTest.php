<?php

namespace Draw\Component\Messenger\Tests\Entity;

use Draw\Component\Messenger\Entity\DrawMessageInterface;
use Draw\Component\Messenger\Entity\DrawMessageTagTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Messenger\Entity\DrawMessageTagTrait
 */
class DrawMessageTagTraitTest extends TestCase
{
    /**
     * @var DrawMessageTagTrait|object
     */
    private object $entity;

    public function setUp(): void
    {
        $this->entity = new class() {
            use DrawMessageTagTrait;
        };
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
            $this->entity->setMessage($value = $this->createMock(DrawMessageInterface::class))
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
