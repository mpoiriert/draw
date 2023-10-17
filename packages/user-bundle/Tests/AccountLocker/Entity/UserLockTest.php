<?php

namespace Draw\Bundle\UserBundle\Tests\AccountLocker\Entity;

use Draw\Bundle\UserBundle\Entity\UserLock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserLock::class)]
class UserLockTest extends TestCase
{
    private UserLock $entity;

    protected function setUp(): void
    {
        $this->entity = new UserLock();
    }

    public function testConstructorDefault(): void
    {
        static::assertNotNull($this->entity->getId(), 'Id will always have a value');
        static::assertNull($this->entity->getUser(), 'User must be null');
        static::assertNull($this->entity->getReason(), 'Reason must be null');
        static::assertInstanceOf(
            \DateTimeInterface::class,
            $this->entity->getCreatedAt(),
            'Created at must be a datetime interface'
        );
        static::assertNull($this->entity->getLockOn(), 'Lock on must be null');
        static::assertNull($this->entity->getExpiresAt(), 'Expires at must be null');
        static::assertNull($this->entity->getUnlockUntil(), 'Unlock until must be null');
    }

    public static function provideTestIsActive(): array
    {
        return [
            'default' => [
                new UserLock(),
                true,
            ],
            'unlock-until-in-the-future' => [
                (new UserLock())->setUnlockUntil(new \DateTime('+ 1 day')),
                false,
            ],
            'unlock-until-in-the-past' => [
                (new UserLock())->setUnlockUntil(new \DateTime('- 1 day')),
                true,
            ],
            'lock-on-in-the-future' => [
                (new UserLock())->setLockOn(new \DateTime('+ 1 day')),
                false,
            ],
            'lock-on-in-the-past' => [
                (new UserLock())->setLockOn(new \DateTime('- 1 day')),
                true,
            ],
            'expires-at-in-the-future' => [
                (new UserLock())->setExpiresAt(new \DateTime('+ 1 day')),
                true,
            ],
            'expires-at-in-the-past' => [
                (new UserLock())->setExpiresAt(new \DateTime('- 1 day')),
                false,
            ],
        ];
    }

    #[DataProvider('provideTestIsActive')]
    public function testIsActive(UserLock $userLock, bool $expected): void
    {
        static::assertSame($expected, $userLock->isActive());
    }
}
