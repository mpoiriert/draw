<?php

namespace Draw\Bundle\UserBundle\Tests\AccountLocker\Entity;

use DateTime;
use DateTimeInterface;
use Draw\Bundle\UserBundle\AccountLocker\Entity\UserLock;
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
        $this->assertNotNull($this->entity->getId(), 'Id will always have a value');
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
}
