<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use App\Sonata\Admin\UserAdmin;
use Draw\Bundle\UserBundle\AccountLocker\Sonata\Admin\UserLockAdmin;
use Draw\Bundle\UserBundle\AccountLocker\Sonata\Controller\RefreshUserLockController;
use Draw\Bundle\UserBundle\AccountLocker\Sonata\Controller\UserLockController;
use Draw\Bundle\UserBundle\AccountLocker\Sonata\Extension\UserAdminExtension;
use Draw\Bundle\UserBundle\Tests\Fixtures\Entity\User;

class DrawUserExtensionWithAccountLockerAndSonataEnabledTest extends DrawUserExtensionWithAccountLockerEnabledTest
{
    public function getConfiguration(): array
    {
        return [
            'user_entity_class' => User::class,
            'account_locker' => [
                'enabled' => true,
                'sonata' => [
                    'enabled' => true,
                ],
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [UserLockAdmin::class];
        yield [RefreshUserLockController::class];
        yield [UserLockController::class];
        yield [UserAdminExtension::class];
    }

    public function testUserAdminExtensionConfiguration(): void
    {
        $this->assertSame(
            UserAdmin::class,
            $this->getContainerBuilder()
                ->getDefinition(UserAdminExtension::class)
                ->getTag('sonata.admin.extension')[0]['target']
        );
    }
}
