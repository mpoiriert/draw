<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\AccountLocker\AccountLocker;
use Draw\Bundle\UserBundle\AccountLocker\Command\RefreshUserLocksCommand;
use Draw\Bundle\UserBundle\AccountLocker\Controller\AccountLockedController;
use Draw\Bundle\UserBundle\AccountLocker\Listener\AccountLockerSubscriber;
use Draw\Bundle\UserBundle\AccountLocker\MessageHandler\RefreshUserLockMessageHandler;
use Draw\Bundle\UserBundle\Tests\Fixtures\Entity\User;

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

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [RefreshUserLocksCommand::class];
        yield [AccountLockedController::class];
        yield [AccountLockerSubscriber::class];
        yield [RefreshUserLockMessageHandler::class];
        yield [AccountLocker::class];
    }
}
