<?php

namespace Draw\Bundle\UserBundle\Tests\Message;

use Draw\Bundle\UserBundle\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\Entity\LockableUserTrait;
use Draw\Bundle\UserBundle\Entity\SecurityUserTrait;
use Draw\Bundle\UserBundle\Message\TemporaryUnlockedMessage;
use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Bundle\UserBundle\Message\TemporaryUnlockedMessage
 */
class TemporaryUnlockedMessageTest extends TestCase
{
    private TemporaryUnlockedMessage $object;

    private bool $wasLocked;

    private \DateTimeInterface $until;

    protected function setUp(): void
    {
        $this->object = new TemporaryUnlockedMessage(
            $this->wasLocked = true,
            $this->until = new \DateTimeImmutable()
        );
    }

    public function testWasLocked(): void
    {
        static::assertSame($this->wasLocked, $this->object->wasLocked());
    }

    public function testUntil(): void
    {
        static::assertSame(
            $this->until->getTimestamp(),
            $this->object->until()->getTimestamp()
        );
    }

    public function testGetUserIdentifier(): void
    {
        static::assertNull($this->object->getUserIdentifier());

        $this->object->preSend(
            new class() implements MessageHolderInterface,
                LockableUserInterface {
                use LockableUserTrait;
                use MessageHolderTrait;
                use SecurityUserTrait;

                public function getUserIdentifier(): string
                {
                    return 'identifier';
                }
            }
        );

        static::assertSame('identifier', $this->object->getUserIdentifier());
    }
}
