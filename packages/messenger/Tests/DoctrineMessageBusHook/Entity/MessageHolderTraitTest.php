<?php

namespace Draw\Component\Messenger\Tests\Entity;

use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderTrait
 */
class MessageHolderTraitTest extends TestCase
{
    use MessageHolderTrait;

    public function testGetOnHoldMessages(): void
    {
        $this->onHoldMessages[\stdClass::class] = $object1 = (object) [];
        $this->onHoldMessages[] = $object2 = (object) [];

        $messages = [$object1, $object2];

        static::assertSame(
            $messages,
            $this->getOnHoldMessages(false)
        );

        static::assertSame(
            $messages,
            $this->getOnHoldMessages(true)
        );

        static::assertEmpty($this->getOnHoldMessages(false));
    }
}
