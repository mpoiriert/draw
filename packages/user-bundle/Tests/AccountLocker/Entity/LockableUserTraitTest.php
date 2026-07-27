<?php

namespace Draw\Bundle\UserBundle\Tests\AccountLocker\Entity;

use Draw\Bundle\UserBundle\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\Entity\LockableUserTrait;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Entity\SecurityUserTrait;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Draw\Bundle\UserBundle\Message\TemporaryUnlockedMessage;
use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(MessageHolderTrait::class)]
class LockableUserTraitTest extends TestCase
{
    private UserStub $object;

    protected function setUp(): void
    {
        $this->object = new UserStub();
    }

    public function testManualLockMutator(): void
    {
        $this->assertFalse(
            $this->object->hasManualLock()
        );

        $this->assertNull(
            $this->object->getLocks()[UserLock::REASON_MANUAL_LOCK] ?? null
        );

        $this->assertSame(
            $this->object,
            $this->object->setManualLock(true)
        );

        $this->assertTrue(
            $this->object->hasManualLock()
        );

        $this->assertNotNull(
            $this->object->getLocks()[UserLock::REASON_MANUAL_LOCK] ?? null
        );

        $this->assertSame(
            $this->object,
            $this->object->setManualLock(false)
        );

        $this->assertNull(
            $this->object->getLocks()[UserLock::REASON_MANUAL_LOCK] ?? null
        );
    }

    public function testLock(): void
    {
        $lock = new UserLock(uniqid('reason-'));

        $this->assertSame(
            $lock,
            $this->object->lock($lock)
        );

        $this->assertTrue($this->object->getUserLocks()->contains($lock));

        $this->assertSame(
            $lock,
            $this->object->lock(clone $lock),
            'Must return the current lock since they are the same'
        );

        $lock->setUnlockUntil(new \DateTimeImmutable());

        $newLock = (clone $lock)->setLockOn(new \DateTimeImmutable());

        $newLock->setUnlockUntil(new \DateTimeImmutable('+ 10 days'));
        $newLock->setExpiresAt(new \DateTimeImmutable('+ 10 days'));
        $newLock->setLockOn(new \DateTimeImmutable('+ 10 days'));

        $this->assertSame(
            $newLock,
            $this->object->lock($newLock),
            'Must return the new lock since they are different'
        );

        $this->assertTrue($this->object->getUserLocks()->contains($newLock));

        $this->assertFalse(
            $this->object->getUserLocks()->contains($lock),
            'Old lock must be remove since the new lock has the same reason'
        );
    }

    public function testUnlock(): void
    {
        $this->assertNull(
            $this->object->unlock(uniqid('reason-')),
            'If not lock is found it just return null'
        );

        $lock = new UserLock(uniqid('reason-'));

        $this->object->lock($lock);

        $this->assertSame(
            $lock,
            $this->object->unlock($lock->getReason(), $until = new \DateTimeImmutable())
        );

        $this->assertTrue(
            $this->object->getUserLocks()->contains($lock),
            'Lock must be kept since we unlock it only until a specific date'
        );

        $this->assertSame(
            $until->getTimestamp(),
            $lock->getUnlockUntil()->getTimestamp(),
        );

        $this->assertSame(
            $lock,
            $this->object->unlock($lock->getReason())
        );

        $this->assertFalse(
            $this->object->getUserLocks()->contains($lock),
            'Lock must be remove from user locks since we remove it completely'
        );

        $this->assertNull(
            $this->object->unlock($lock->getReason())
        );
    }

    public function testGeLocks(): void
    {
        $this->assertEmpty($this->object->getLocks());

        $this->object->setManualLock(true);

        $locks = $this->object->getLocks();

        $this->assertCount(1, $locks);

        /** @var UserLock $lock */
        $lock = current($locks);

        $this->assertArrayHasKey($lock->getReason(), $locks);
    }

    public function testIsLocked(): void
    {
        $this->assertFalse($this->object->isLocked());

        $this->object->setManualLock(true);

        $this->assertTrue($this->object->isLocked());

        /** @var UserLock $lock */
        $lock = current($this->object->getLocks());

        $lock->setUnlockUntil(new \DateTimeImmutable('+ 1 days'));

        $this->assertFalse($this->object->isLocked());
    }

    public function testUserLockMutator(): void
    {
        $this->assertCount(0, $this->object->getUserLocks());

        $this->assertSame(
            $this->object,
            $this->object->addUserLock($value = new UserLock(uniqid('reason-')))
        );

        $this->assertSame(
            $value->getId(),
            $this->object->getOnHoldMessages(true)[0]->getUserLockId()
        );

        $this->assertCount(1, $this->object->getUserLocks());
        $this->assertSame(
            $value,
            $this->object->getUserLocks()[0]
        );

        $this->assertSame(
            $this->object,
            $value->getUser()
        );

        $this->assertSame(
            $this->object,
            $this->object->removeUserLock($value)
        );

        $this->assertCount(0, $this->object->getUserLocks());
    }

    public function tesTemporaryUnlockAll(): void
    {
        $this->object->temporaryUnlockAll($until = new \DateTimeImmutable());

        $message = $this->object->getOnHoldMessages(true)[0];

        $this->assertInstanceOf(
            TemporaryUnlockedMessage::class,
            $message
        );

        $this->assertSame(
            $until->getTimestamp(),
            $message->until()->getTimestamp()
        );

        $this->assertFalse($message->wasLocked());

        $this->object->setManualLock(true);

        $this->object->temporaryUnlockAll($until = new \DateTimeImmutable(' + 10 hours'));

        $message = $this->object->getOnHoldMessages(true)[0];

        $this->assertInstanceOf(
            TemporaryUnlockedMessage::class,
            $message
        );

        $this->assertSame(
            $until->getTimestamp(),
            $message->until()->getTimestamp()
        );

        $this->assertTrue($message->wasLocked());
    }

    public function testAddUserLockPreventManualLockIsFalse(): void
    {
        $this->object->setManualLock(false);

        $this->object->addUserLock(new UserLock(UserLock::REASON_MANUAL_LOCK));

        $this->assertEmpty($this->object->getLocks(), 'Manual lock should not be added if set to false');
    }

    public function testRemoveUserLockPreventManualLockIsTrue(): void
    {
        $this->object->setManualLock(true);

        $this->object->addUserLock($lock = new UserLock(UserLock::REASON_MANUAL_LOCK));

        $this->object->removeUserLock($lock);

        $this->assertCount(1, $this->object->getLocks(), 'Manual lock should not be removed if set to false');
    }
}

class UserStub implements SecurityUserInterface, LockableUserInterface
{
    use LockableUserTrait;
    use MessageHolderTrait;
    use SecurityUserTrait;

    public function getId(): string
    {
        return '';
    }
}
