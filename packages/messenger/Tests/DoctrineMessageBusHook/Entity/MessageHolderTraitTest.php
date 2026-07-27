<?php

namespace Draw\Component\Messenger\Tests\DoctrineMessageBusHook\Entity;

use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(MessageHolderTrait::class)]
class MessageHolderTraitTest extends TestCase
{
    use MessageHolderTrait;

    public function testGetOnHoldMessages(): void
    {
        $this->onHoldMessages[\stdClass::class] = $object1 = (object) [];
        $this->onHoldMessages[] = $object2 = (object) [];

        $messages = [$object1, $object2];

        $this->assertSame(
            $messages,
            $this->getOnHoldMessages(false)
        );

        $this->assertSame(
            $messages,
            $this->getOnHoldMessages(true)
        );

        $this->assertEmpty($this->getOnHoldMessages(false));
    }
}
