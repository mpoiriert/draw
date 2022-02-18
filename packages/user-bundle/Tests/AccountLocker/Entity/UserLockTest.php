<?php

namespace Draw\Bundle\UserBundle\Tests\AccountLocker\Entity;

use DateTime;
use DateTimeInterface;
use Draw\Bundle\UserBundle\AccountLocker\Entity\UserLock;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Bundle\UserBundle\AccountLocker\Entity\UserLock
 */
class UserLockTest extends TestCase
{
    private $entity;

    public function setUp(): void
    {
        $this->entity = new UserLock();
    }

    public function testConstructorDefault(): void
    {
        $this->assertNull($this->entity->getId(), 'Id must be null');
        $this->assertNull($this->entity->getUser(), 'User must be null');
        $this->assertNull($this->entity->getReason(), 'Reason must be null');
        $this->assertInstanceOf(
            DateTimeInterface::class,
            $this->entity->getCreatedAt(),
            'Created at must be a datetime interface'
        );
        $this->assertNull($this->entity->getLockOn(), 'Lock on must be null');
        $this->assertNull($this->entity->getExpiresAt(), 'Expires at must be null');
        $this->assertNull($this->entity->getUnlockUntil(), 'Unlock until must be null');
    }

    public function provideTestIsActive(): array
    {
        return [
            'default' => [
                new UserLock(),
                true,
            ],
            'unlock-until-in-the-future' => [
                (new UserLock())->setUnlockUntil(new DateTime('+ 1 day')),
                false,
            ],
            'unlock-until-in-the-past' => [
                (new UserLock())->setUnlockUntil(new DateTime('- 1 day')),
                true,
            ],
            'lock-on-in-the-future' => [
                (new UserLock())->setLockOn(new DateTime('+ 1 day')),
                false,
            ],
            'lock-on-in-the-past' => [
                (new UserLock())->setLockOn(new DateTime('- 1 day')),
                true,
            ],
            'expires-at-in-the-future' => [
                (new UserLock())->setExpiresAt(new DateTime('+ 1 day')),
                true,
            ],
            'expires-at-in-the-past' => [
                (new UserLock())->setExpiresAt(new DateTime('- 1 day')),
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideTestIsActive
     */
    public function testIsActive(UserLock $userLock, bool $expected): void
    {
        $this->assertSame($expected, $userLock->isActive());
    }

    public function testMerge(): void
    {
        $preservedDate = new DateTime('2000-01-01 00:00:00');
        $mergedDate = new DateTime('3000-01-01 00:00:00');

        $this->entity
            ->setId(1)
            ->setExpiresAt($preservedDate)
            ->setLockOn($preservedDate)
            ->setUnlockUntil($preservedDate)
            ->setCreatedAt($preservedDate)
            ->merge(
                (new UserLock())
                    ->setId(2)
                    ->setExpiresAt($mergedDate)
                    ->setLockOn($mergedDate)
                    ->setUnlockUntil($mergedDate)
                    ->setCreatedAt($mergedDate)
            );

        $this->assertSame(
            1,
            $this->entity->getId(),
            '[id] must be preserved'
        );

        $this->assertSame(
            $mergedDate->getTimestamp(),
            $this->entity->getExpiresAt()->getTimestamp(),
            '[expiresAt] must be merged'
        );

        $this->assertSame(
            $mergedDate->getTimestamp(),
            $this->entity->getLockOn()->getTimestamp(),
            '[lockOn] must be merged'
        );

        $this->assertSame(
            $preservedDate->getTimestamp(),
            $this->entity->getUnlockUntil()->getTimestamp(),
            '[unlockUntil] must be preserved'
        );

        $this->assertSame(
            $preservedDate->getTimestamp(),
            $this->entity->getCreatedAt()->getTimestamp(),
            '[createdAt] must be preserved'
        );
    }

    public function testMergeDifferentReasonThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Cannot merge lock [original-reason] with lock [new-reason]'
        );

        $this->entity->setReason('original-reason')
            ->merge(new UserLock('new-reason'));
    }
}
