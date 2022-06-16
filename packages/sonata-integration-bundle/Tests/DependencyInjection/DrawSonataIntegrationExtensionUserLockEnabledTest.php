<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use App\Sonata\Admin\UserAdmin;
use Draw\Bundle\SonataIntegrationBundle\User\Admin\Extension\RefreshUserLockExtension;
use Draw\Bundle\SonataIntegrationBundle\User\Admin\Extension\UnlockUserLockExtension;
use Draw\Bundle\SonataIntegrationBundle\User\Admin\UserLockAdmin;
use Draw\Bundle\SonataIntegrationBundle\User\Controller\RefreshUserLockController;

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
        yield ['draw.sonata.user.action.unlock_user_action'];
        yield [UnlockUserLockExtension::class];
        yield [RefreshUserLockExtension::class];
    }

    public function testUserAdminExtensionConfiguration(): void
    {
        $this->assertSame(
            UserAdmin::class,
            $this->getContainerBuilder()
                ->getDefinition(UnlockUserLockExtension::class)
                ->getTag('sonata.admin.extension')[0]['target']
        );

        $this->assertSame(
            UserAdmin::class,
            $this->getContainerBuilder()
                ->getDefinition(RefreshUserLockExtension::class)
                ->getTag('sonata.admin.extension')[0]['target']
        );
    }
}
