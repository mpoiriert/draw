<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use App\Sonata\Admin\UserAdmin;
use Draw\Bundle\SonataIntegrationBundle\User\Admin\UserLockAdmin;
use Draw\Bundle\SonataIntegrationBundle\User\Controller\RefreshUserLockController;
use Draw\Bundle\SonataIntegrationBundle\User\Controller\UserLockUnlockController;
use Draw\Bundle\SonataIntegrationBundle\User\Extension\UserLockExtension;

/**
 * @covers \Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension
 */
class DrawSonataIntegrationExtensionUserLockEnabledTest extends DrawSonataIntegrationExtensionUserEnabledTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['user']['user_lock'] = [
            'enabled' => true,
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [UserLockAdmin::class];
        yield [RefreshUserLockController::class];
        yield [UserLockUnlockController::class];
        yield [UserLockExtension::class];
    }

    public function testUserAdminExtensionConfiguration(): void
    {
        $this->assertSame(
            UserAdmin::class,
            $this->getContainerBuilder()
                ->getDefinition(UserLockExtension::class)
                ->getTag('sonata.admin.extension')[0]['target']
        );
    }
}
