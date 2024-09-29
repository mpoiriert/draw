<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\AccountLocker;
use Draw\Bundle\UserBundle\Command\RefreshUserLocksCommand;
use Draw\Bundle\UserBundle\EventListener\AccountLockerListener;
use Draw\Bundle\UserBundle\MessageHandler\RefreshUserLockMessageHandler;
use Draw\Bundle\UserBundle\MessageHandler\UserLockLifeCycleMessageHandler;
use Draw\Bundle\UserBundle\Tests\Fixtures\Entity\User;

/**
 * @internal
 */
class DrawUserExtensionWithAccountLockerEnabledTest extends DrawUserExtensionTest
{
    public function getConfiguration(): array
    {
        return [
            'user_entity_class' => User::class,
            'account_locker' => [
                'enabled' => true,
            ],
        ];
    }

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [AccountLocker::class];
        yield [AccountLockerListener::class];
        yield [RefreshUserLocksCommand::class];
        yield [RefreshUserLockMessageHandler::class];
        yield [UserLockLifeCycleMessageHandler::class];
    }

    public function testExcludePathsParameter(): void
    {
        static::assertSame(
            [],
            $this
                ->getContainerBuilder()
                ->getParameter('draw.user.orm.metadata_driver.exclude_paths')
        );
    }
}
